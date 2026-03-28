<?php
namespace GlassPress\Core;

/**
 * View/Template rendering engine.
 */
class View
{
    private static ?string $layoutPath = null;
    private static array $sections = [];
    private static ?string $currentSection = null;
    private static array $shared = [];

    /**
     * Render a view file with data.
     */
    public static function render(string $view, array $data = [], ?string $layout = null): string
    {
        // Clear sections from any previous render
        self::$sections = [];
        self::$currentSection = null;

        $viewPath = self::resolveViewPath($view);

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View not found: {$view} ({$viewPath})");
        }

        $data = array_merge(self::$shared, $data);

        // Add common helpers
        $data['csrf_field'] = CSRF::field();
        $data['csrf_token'] = CSRF::generate();
        $data['app'] = Application::getInstance();

        ob_start();
        extract($data, EXTR_SKIP);
        require $viewPath;
        $content = ob_get_clean();

        if ($layout) {
            // Only set content section if the view didn't already define it via startSection/endSection
            if (!isset(self::$sections['content'])) {
                self::$sections['content'] = $content;
            }
            $layoutPath = self::resolveViewPath($layout);
            if (file_exists($layoutPath)) {
                ob_start();
                extract($data, EXTR_SKIP);
                require $layoutPath;
                $content = ob_get_clean();
            }
        }

        return $content;
    }

    /**
     * Output a rendered view.
     */
    public static function display(string $view, array $data = [], ?string $layout = null): void
    {
        echo self::render($view, $data, $layout);
    }

    /**
     * Include a partial view.
     */
    public static function partial(string $view, array $data = []): void
    {
        $viewPath = self::resolveViewPath($view);
        if (file_exists($viewPath)) {
            $data = array_merge(self::$shared, $data);
            extract($data, EXTR_SKIP);
            require $viewPath;
        }
    }

    /**
     * Start a section.
     */
    public static function startSection(string $name): void
    {
        self::$currentSection = $name;
        ob_start();
    }

    /**
     * End a section.
     */
    public static function endSection(): void
    {
        if (self::$currentSection !== null) {
            self::$sections[self::$currentSection] = ob_get_clean();
            self::$currentSection = null;
        }
    }

    /**
     * Yield a section's content.
     */
    public static function yieldSection(string $name, string $default = ''): string
    {
        return self::$sections[$name] ?? $default;
    }

    /**
     * Share data with all views.
     */
    public static function share(string $key, mixed $value): void
    {
        self::$shared[$key] = $value;
    }

    /**
     * Escape HTML.
     */
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Resolve a dot-notation view path.
     */
    private static function resolveViewPath(string $view): string
    {
        // Check for admin views
        if (str_starts_with($view, 'admin.')) {
            $path = str_replace('.', '/', substr($view, 6));
            return GLASSPRESS_ROOT . '/app/Admin/Views/' . $path . '.php';
        }

        // Check for installer views
        if (str_starts_with($view, 'install.')) {
            $path = str_replace('.', '/', substr($view, 8));
            return GLASSPRESS_ROOT . '/app/Install/Views/' . $path . '.php';
        }

        // Check for theme views
        if (str_starts_with($view, 'theme.')) {
            $path = str_replace('.', '/', substr($view, 6));
            $app = Application::getInstance();
            $theme = $app->getService('settings')?->get('active_theme', 'flavor') ?? 'flavor';
            return GLASSPRESS_ROOT . '/themes/' . $theme . '/' . $path . '.php';
        }

        // Default views directory
        $path = str_replace('.', '/', $view);
        return GLASSPRESS_ROOT . '/app/Views/' . $path . '.php';
    }
}
