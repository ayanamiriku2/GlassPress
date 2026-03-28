<?php
namespace GlassPress\Core;

/**
 * Main Application Container & Bootstrap
 */
class Application
{
    private static ?self $instance = null;
    private array $services = [];
    private array $config = [];
    private bool $installed = false;

    private function __construct() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function boot(): void
    {
        // Error handling
        $this->setupErrorHandling();

        // Session
        if (session_status() === PHP_SESSION_NONE) {
            $sessionPath = GLASSPRESS_ROOT . '/storage/sessions';
            if (is_dir($sessionPath) && is_writable($sessionPath)) {
                session_save_path($sessionPath);
            }
            session_name('glasspress_session');
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
                'secure' => $this->isHttps(),
            ]);
            session_start();
        }

        // Check installation status
        $this->installed = $this->checkInstalled();

        if ($this->installed) {
            $this->loadConfig();
            $this->bootServices();
        }

        // Register hooks system
        $this->registerService('hooks', new Hooks());
    }

    public function handleRequest(): void
    {
        $uri = $this->getRequestUri();
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Not installed → redirect to installer
        if (!$this->installed) {
            $installer = new \GlassPress\App\Install\InstallerController($this);
            $installer->handle($uri, $method);
            return;
        }

        // Load router and dispatch
        $router = $this->getService('router');
        
        // Load routes
        $this->loadRoutes($router);

        // Execute hooks before dispatch
        $this->getService('hooks')->doAction('before_dispatch', $uri, $method);

        $router->dispatch($method, $uri);
    }

    private function loadRoutes(Router $router): void
    {
        $routeFiles = [
            GLASSPRESS_ROOT . '/app/Routes/web.php',
            GLASSPRESS_ROOT . '/app/Routes/admin.php',
            GLASSPRESS_ROOT . '/app/Routes/api.php',
        ];

        foreach ($routeFiles as $file) {
            if (file_exists($file)) {
                $app = $this;
                require_once $file;
            }
        }
    }

    private function setupErrorHandling(): void
    {
        error_reporting(E_ALL);
        
        $debug = $this->installed && isset($this->config['debug']) && $this->config['debug'];
        
        if ($debug) {
            ini_set('display_errors', '1');
        } else {
            ini_set('display_errors', '0');
            ini_set('log_errors', '1');
            $logDir = GLASSPRESS_ROOT . '/storage/logs';
            if (is_dir($logDir)) {
                ini_set('error_log', $logDir . '/error.log');
            }
        }

        set_exception_handler(function (\Throwable $e) {
            $this->handleException($e);
        });
    }

    private function handleException(\Throwable $e): void
    {
        $debug = $this->config['debug'] ?? false;
        
        if (!headers_sent()) {
            http_response_code(500);
        }

        if ($debug) {
            echo '<h1>Error</h1>';
            echo '<p>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
            echo '<pre>' . htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8') . '</pre>';
        } else {
            echo '<h1>An error occurred</h1><p>Please try again later.</p>';
        }

        error_log('GlassPress Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    }

    private function checkInstalled(): bool
    {
        $configFile = GLASSPRESS_ROOT . '/config/app.php';
        $lockFile = GLASSPRESS_ROOT . '/storage/.installed';
        return file_exists($configFile) && file_exists($lockFile);
    }

    private function loadConfig(): void
    {
        $configFile = GLASSPRESS_ROOT . '/config/app.php';
        if (file_exists($configFile)) {
            $this->config = require $configFile;
        }
    }

    private function bootServices(): void
    {
        // Database
        $db = new Database($this->config['database'] ?? []);
        $this->registerService('db', $db);

        // Settings (loads from DB)
        $settings = new Settings($db);
        $this->registerService('settings', $settings);

        // Router
        $router = new Router($this);
        $this->registerService('router', $router);

        // Auth
        $auth = new Auth($db);
        $this->registerService('auth', $auth);

        // Cache
        $cache = new Cache(GLASSPRESS_ROOT . '/storage/cache');
        $this->registerService('cache', $cache);

        // Media
        $media = new Media($db, $this->config);
        $this->registerService('media', $media);
    }

    public function registerService(string $name, object $service): void
    {
        $this->services[$name] = $service;
    }

    public function getService(string $name): ?object
    {
        return $this->services[$name] ?? null;
    }

    public function getConfig(string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->config;
        }

        $keys = explode('.', $key);
        $value = $this->config;
        
        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    public function isInstalled(): bool
    {
        return $this->installed;
    }

    public function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ($_SERVER['SERVER_PORT'] ?? 80) == 443
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    }

    public function getBaseUrl(): string
    {
        $protocol = $this->isHttps() ? 'https' : 'http';
        $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        return $protocol . '://' . $host . $basePath;
    }

    private function getRequestUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Remove query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        // Remove base path
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        if ($basePath && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }

        $uri = '/' . ltrim($uri, '/');
        
        // Remove trailing slash (except root)
        if ($uri !== '/' && str_ends_with($uri, '/')) {
            $uri = rtrim($uri, '/');
        }

        return rawurldecode($uri);
    }

    public function getSiteUrl(string $path = ''): string
    {
        $base = $this->getBaseUrl();
        return rtrim($base, '/') . ($path ? '/' . ltrim($path, '/') : '');
    }

    public function getAdminUrl(string $path = ''): string
    {
        $adminPath = 'admin' . ($path ? '/' . ltrim($path, '/') : '');
        return $this->getSiteUrl($adminPath);
    }
}
