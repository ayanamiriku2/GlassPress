<?php
namespace GlassPress\App\Install;

use GlassPress\Core\Application;
use GlassPress\Core\Auth;
use GlassPress\Core\Database;
use GlassPress\Core\Schema;
use GlassPress\Core\CSRF;

/**
 * Installation wizard controller.
 */
class InstallerController
{
    private Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function handle(string $uri, string $method): void
    {
        // If already installed, block access
        if ($this->app->isInstalled()) {
            header('Location: /');
            exit;
        }

        if ($method === 'POST' && ($uri === '/install' || $uri === '/')) {
            $this->processInstall();
            return;
        }

        if ($method === 'POST' && $uri === '/install/test-db') {
            $this->testDatabase();
            return;
        }

        $this->showInstaller();
    }

    private function showInstaller(): void
    {
        $data = [
            'csrf_field' => CSRF::field(),
            'csrf_token' => CSRF::generate(),
            'timezones' => \DateTimeZone::listIdentifiers(),
            'errors' => $_SESSION['install_errors'] ?? [],
            'old' => $_SESSION['install_old'] ?? [],
        ];

        unset($_SESSION['install_errors'], $_SESSION['install_old']);

        // Render installer view
        ob_start();
        extract($data, EXTR_SKIP);
        require GLASSPRESS_ROOT . '/app/Install/Views/installer.php';
        echo ob_get_clean();
    }

    private function testDatabase(): void
    {
        header('Content-Type: application/json');

        $config = [
            'host' => trim($_POST['db_host'] ?? '127.0.0.1'),
            'port' => (int) ($_POST['db_port'] ?? 3306),
            'name' => trim($_POST['db_name'] ?? ''),
            'username' => trim($_POST['db_username'] ?? ''),
            'password' => $_POST['db_password'] ?? '',
        ];

        $error = Database::testConnection($config);

        echo json_encode([
            'success' => $error === null,
            'message' => $error ?? 'Connection successful!',
        ]);
        exit;
    }

    private function processInstall(): void
    {
        // Verify CSRF
        $token = $_POST['_csrf_token'] ?? '';
        if (!CSRF::verify($token)) {
            $this->redirectWithErrors(['Invalid security token. Please try again.']);
            return;
        }

        // Collect and validate inputs
        $data = [
            'db_host' => trim($_POST['db_host'] ?? '127.0.0.1'),
            'db_port' => (int) ($_POST['db_port'] ?? 3306),
            'db_name' => trim($_POST['db_name'] ?? ''),
            'db_username' => trim($_POST['db_username'] ?? ''),
            'db_password' => $_POST['db_password'] ?? '',
            'db_prefix' => preg_replace('/[^a-z0-9_]/i', '', $_POST['db_prefix'] ?? 'gp_'),
            'site_title' => trim($_POST['site_title'] ?? ''),
            'site_tagline' => trim($_POST['site_tagline'] ?? ''),
            'admin_username' => trim($_POST['admin_username'] ?? ''),
            'admin_email' => trim($_POST['admin_email'] ?? ''),
            'admin_password' => $_POST['admin_password'] ?? '',
            'timezone' => trim($_POST['timezone'] ?? 'UTC'),
            'language' => trim($_POST['language'] ?? 'en'),
            'install_sample' => isset($_POST['install_sample']),
        ];

        // Validate
        $errors = $this->validate($data);
        if (!empty($errors)) {
            $this->redirectWithErrors($errors, $data);
            return;
        }

        // Test database connection
        $dbConfig = [
            'host' => $data['db_host'],
            'port' => $data['db_port'],
            'name' => $data['db_name'],
            'username' => $data['db_username'],
            'password' => $data['db_password'],
        ];

        $dbError = Database::testConnection($dbConfig);
        if ($dbError !== null) {
            $this->redirectWithErrors(['Database connection failed: ' . $dbError], $data);
            return;
        }

        try {
            // 1. Create config file
            $this->generateConfig($data);

            // 2. Create database tables
            $dbConfig['prefix'] = $data['db_prefix'];
            $dbConfig['charset'] = 'utf8mb4';
            $db = new Database($dbConfig);
            $this->createTables($db, $data['db_prefix']);

            // 3. Seed default settings
            $this->seedSettings($db, $data);

            // 4. Create admin user
            $this->createAdmin($db, $data);

            // 5. Install sample content if requested
            if ($data['install_sample']) {
                $this->installSampleContent($db, $data);
            }

            // 6. Create default menu
            $this->createDefaultMenu($db);

            // 7. Lock installation
            $this->lockInstallation();

            // 8. Redirect to login
            CSRF::regenerate();
            header('Location: /admin/login?installed=1');
            exit;

        } catch (\Exception $e) {
            // Cleanup on failure
            @unlink(GLASSPRESS_ROOT . '/config/app.php');
            @unlink(GLASSPRESS_ROOT . '/storage/.installed');
            $this->redirectWithErrors(['Installation failed: ' . $e->getMessage()], $data);
        }
    }

    private function validate(array $data): array
    {
        $errors = [];

        if (empty($data['db_name'])) {
            $errors[] = 'Database name is required.';
        }
        if (empty($data['db_username'])) {
            $errors[] = 'Database username is required.';
        }
        if (empty($data['site_title'])) {
            $errors[] = 'Site title is required.';
        }
        if (empty($data['admin_username'])) {
            $errors[] = 'Admin username is required.';
        }
        if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $data['admin_username'])) {
            $errors[] = 'Admin username must be 3-30 characters (letters, numbers, underscores).';
        }
        if (empty($data['admin_email']) || !filter_var($data['admin_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid admin email is required.';
        }
        if (empty($data['admin_password']) || strlen($data['admin_password']) < 8) {
            $errors[] = 'Admin password must be at least 8 characters.';
        }
        if (empty($data['db_prefix']) || !preg_match('/^[a-z0-9_]+$/i', $data['db_prefix'])) {
            $errors[] = 'Table prefix must contain only letters, numbers, and underscores.';
        }

        // Check writable directories
        $dirs = ['/config', '/storage', '/storage/cache', '/storage/logs', '/storage/sessions', '/uploads'];
        foreach ($dirs as $dir) {
            $path = GLASSPRESS_ROOT . $dir;
            if (!is_dir($path) || !is_writable($path)) {
                $errors[] = "Directory '{$dir}' must be writable.";
            }
        }

        return $errors;
    }

    private function generateConfig(array $data): void
    {
        $securityKey = bin2hex(random_bytes(32));
        $authSalt = bin2hex(random_bytes(32));

        $config = <<<PHP
<?php
/**
 * GlassPress Configuration
 * Generated during installation on {$this->now()}
 * 
 * SECURITY: Do not share this file or expose it publicly.
 */

return [
    'debug' => false,

    'database' => [
        'host' => '{$this->escapeConfig($data['db_host'])}',
        'port' => {$data['db_port']},
        'name' => '{$this->escapeConfig($data['db_name'])}',
        'username' => '{$this->escapeConfig($data['db_username'])}',
        'password' => '{$this->escapeConfig($data['db_password'])}',
        'prefix' => '{$this->escapeConfig($data['db_prefix'])}',
        'charset' => 'utf8mb4',
    ],

    'security' => [
        'key' => '{$securityKey}',
        'auth_salt' => '{$authSalt}',
    ],

    'uploads' => [
        'max_size' => 10485760,
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'zip', 'doc', 'docx', 'xls', 'xlsx', 'mp4', 'mp3'],
    ],
];
PHP;

        $configDir = GLASSPRESS_ROOT . '/config';
        if (!is_dir($configDir)) {
            mkdir($configDir, 0755, true);
        }

        file_put_contents($configDir . '/app.php', $config, LOCK_EX);
        chmod($configDir . '/app.php', 0640);
    }

    private function createTables(Database $db, string $prefix): void
    {
        $tables = Schema::getTables($prefix);
        foreach ($tables as $sql) {
            $db->getPdo()->exec($sql);
        }
    }

    private function seedSettings(Database $db, array $data): void
    {
        $defaults = Schema::getDefaultSettings();
        
        // Override with user input
        $defaults['site_title'] = $data['site_title'];
        $defaults['site_tagline'] = $data['site_tagline'];
        $defaults['admin_email'] = $data['admin_email'];
        $defaults['timezone'] = $data['timezone'];
        $defaults['language'] = $data['language'];

        // Detect site URL
        $protocol = $this->app->isHttps() ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        $defaults['site_url'] = $protocol . '://' . $host . $basePath;

        // Update sitemap reference in robots.txt
        $siteUrl = $defaults['site_url'];
        $defaults['robots_txt'] .= "\nSitemap: {$siteUrl}/sitemap.xml\n";

        foreach ($defaults as $key => $value) {
            $db->insert('settings', [
                'setting_key' => $key,
                'setting_value' => is_array($value) ? json_encode($value) : (string) $value,
                'autoload' => 1,
            ]);
        }
    }

    private function createAdmin(Database $db, array $data): void
    {
        $db->insert('users', [
            'username' => $data['admin_username'],
            'email' => $data['admin_email'],
            'password' => Auth::hashPassword($data['admin_password']),
            'display_name' => $data['admin_username'],
            'role' => 'administrator',
            'status' => 'active',
            'created_at' => $this->now(),
            'updated_at' => $this->now(),
        ]);
    }

    private function installSampleContent(Database $db, array $data): void
    {
        $sample = Schema::getSampleContent();
        $now = $this->now();

        // Categories
        foreach ($sample['categories'] as $cat) {
            $db->insert('taxonomies', [
                'name' => $cat['name'],
                'slug' => $cat['slug'],
                'taxonomy' => 'category',
                'description' => $cat['description'] ?? '',
                'count' => 0,
            ]);
        }

        // Tags
        foreach ($sample['tags'] as $tag) {
            $db->insert('taxonomies', [
                'name' => $tag['name'],
                'slug' => $tag['slug'],
                'taxonomy' => 'tag',
                'description' => '',
                'count' => 0,
            ]);
        }

        // Posts
        foreach ($sample['posts'] as $post) {
            $postId = $db->insert('posts', [
                'author_id' => 1,
                'title' => $post['title'],
                'slug' => $post['slug'],
                'content' => $post['content'],
                'excerpt' => $post['excerpt'],
                'status' => $post['status'],
                'post_type' => $post['post_type'],
                'published_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Assign to Uncategorized category
            $db->insert('post_taxonomy', [
                'post_id' => $postId,
                'taxonomy_id' => 1,
            ]);

            // Update category count
            $db->update('taxonomies', ['count' => 1], 'id = ?', [1]);
        }

        // Pages
        foreach ($sample['pages'] as $page) {
            $db->insert('posts', [
                'author_id' => 1,
                'title' => $page['title'],
                'slug' => $page['slug'],
                'content' => $page['content'],
                'excerpt' => $page['excerpt'] ?? '',
                'status' => $page['status'],
                'post_type' => $page['post_type'],
                'published_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function createDefaultMenu(Database $db): void
    {
        $menuId = $db->insert('menus', [
            'name' => 'Main Menu',
            'slug' => 'main-menu',
            'location' => 'primary',
            'created_at' => $this->now(),
        ]);

        $db->insert('menu_items', [
            'menu_id' => $menuId,
            'title' => 'Home',
            'url' => '/',
            'item_type' => 'custom',
            'sort_order' => 0,
        ]);
    }

    private function lockInstallation(): void
    {
        file_put_contents(
            GLASSPRESS_ROOT . '/storage/.installed',
            'Installed on ' . $this->now() . "\nVersion: " . GLASSPRESS_VERSION,
            LOCK_EX
        );
    }

    private function now(): string
    {
        return date('Y-m-d H:i:s');
    }

    private function escapeConfig(string $value): string
    {
        return addcslashes($value, "'\\");
    }

    private function redirectWithErrors(array $errors, array $old = []): void
    {
        $_SESSION['install_errors'] = $errors;
        // Don't store password in session
        unset($old['admin_password'], $old['db_password']);
        $_SESSION['install_old'] = $old;
        header('Location: /');
        exit;
    }
}
