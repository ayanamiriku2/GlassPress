<?php
namespace GlassPress\Core;

/**
 * Lightweight router with named routes, groups, and middleware support.
 */
class Router
{
    private array $routes = [];
    private array $namedRoutes = [];
    private array $groupStack = [];
    private Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function get(string $path, callable|array|string $handler, ?string $name = null): self
    {
        return $this->addRoute('GET', $path, $handler, $name);
    }

    public function post(string $path, callable|array|string $handler, ?string $name = null): self
    {
        return $this->addRoute('POST', $path, $handler, $name);
    }

    public function put(string $path, callable|array|string $handler, ?string $name = null): self
    {
        return $this->addRoute('PUT', $path, $handler, $name);
    }

    public function delete(string $path, callable|array|string $handler, ?string $name = null): self
    {
        return $this->addRoute('DELETE', $path, $handler, $name);
    }

    public function any(string $path, callable|array|string $handler, ?string $name = null): self
    {
        foreach (['GET', 'POST', 'PUT', 'DELETE'] as $method) {
            $this->addRoute($method, $path, $handler, $name);
        }
        return $this;
    }

    public function group(array $attributes, callable $callback): self
    {
        $this->groupStack[] = $attributes;
        $callback($this);
        array_pop($this->groupStack);
        return $this;
    }

    private function addRoute(string $method, string $path, callable|array|string $handler, ?string $name = null): self
    {
        // Apply group attributes
        $prefix = '';
        $middleware = [];
        foreach ($this->groupStack as $group) {
            if (isset($group['prefix'])) {
                $prefix .= '/' . trim($group['prefix'], '/');
            }
            if (isset($group['middleware'])) {
                $mw = is_array($group['middleware']) ? $group['middleware'] : [$group['middleware']];
                $middleware = array_merge($middleware, $mw);
            }
        }

        $fullPath = $prefix . '/' . ltrim($path, '/');
        $fullPath = '/' . trim($fullPath, '/');
        if ($fullPath !== '/') {
            $fullPath = rtrim($fullPath, '/');
        }

        $route = [
            'method' => $method,
            'path' => $fullPath,
            'handler' => $handler,
            'middleware' => $middleware,
            'pattern' => $this->compilePattern($fullPath),
        ];

        $this->routes[] = $route;

        if ($name) {
            $this->namedRoutes[$name] = $route;
        }

        return $this;
    }

    private function compilePattern(string $path): string
    {
        // Convert {param} to named capture groups
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        // Convert {param?} to optional groups
        $pattern = preg_replace('/\{([a-zA-Z_]+)\?\}/', '(?P<$1>[^/]*)?', $pattern);
        return '#^' . $pattern . '$#';
    }

    public function dispatch(string $method, string $uri): void
    {
        // Handle HEAD as GET
        if ($method === 'HEAD') {
            $method = 'GET';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                // Extract named params
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Run middleware
                foreach ($route['middleware'] as $middleware) {
                    $result = $this->runMiddleware($middleware, $params);
                    if ($result === false) {
                        return;
                    }
                }

                // Execute handler
                $this->executeHandler($route['handler'], $params);
                return;
            }
        }

        // No route found - try frontend routing (permalink-based)
        $this->handleFrontendRoute($uri);
    }

    private function runMiddleware(string $middleware, array $params): bool
    {
        return match ($middleware) {
            'auth' => $this->authMiddleware(),
            'admin' => $this->adminMiddleware(),
            'csrf' => $this->csrfMiddleware(),
            'guest' => $this->guestMiddleware(),
            default => true,
        };
    }

    private function authMiddleware(): bool
    {
        $auth = $this->app->getService('auth');
        if (!$auth || !$auth->check()) {
            $loginUrl = $this->app->getSiteUrl('admin/login');
            $returnUrl = urlencode($_SERVER['REQUEST_URI'] ?? '/');
            header("Location: {$loginUrl}?redirect={$returnUrl}");
            exit;
        }
        return true;
    }

    private function adminMiddleware(): bool
    {
        if (!$this->authMiddleware()) {
            return false;
        }
        $auth = $this->app->getService('auth');
        if (!$auth->hasCapability('access_admin')) {
            http_response_code(403);
            echo '<h1>403 Forbidden</h1>';
            return false;
        }
        return true;
    }

    private function csrfMiddleware(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return true;
        }
        $token = $_POST['_csrf_token'] ?? $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!CSRF::verify($token)) {
            http_response_code(403);
            echo '<h1>403 - Invalid CSRF Token</h1>';
            return false;
        }
        return true;
    }

    private function guestMiddleware(): bool
    {
        $auth = $this->app->getService('auth');
        if ($auth && $auth->check()) {
            header('Location: ' . $this->app->getAdminUrl());
            exit;
        }
        return true;
    }

    private function executeHandler(callable|array|string $handler, array $params): void
    {
        if (is_string($handler) && str_contains($handler, '@')) {
            [$class, $method] = explode('@', $handler, 2);
            $instance = new $class($this->app);
            $handler = [$instance, $method];
        } elseif (is_array($handler) && is_string($handler[0])) {
            $handler[0] = new $handler[0]($this->app);
        }

        call_user_func_array($handler, $params);
    }

    private function handleFrontendRoute(string $uri): void
    {
        $frontend = new \GlassPress\App\Controllers\FrontendController($this->app);
        $frontend->resolve($uri);
    }

    public function url(string $name, array $params = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            return '#';
        }
        $path = $this->namedRoutes[$name]['path'];
        foreach ($params as $key => $value) {
            $path = str_replace('{' . $key . '}', $value, $path);
        }
        return $this->app->getSiteUrl(ltrim($path, '/'));
    }
}
