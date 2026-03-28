<?php
namespace GlassPress\App\Admin\Controllers;

use GlassPress\Core\Application;
use GlassPress\Core\View;
use GlassPress\Core\CSRF;

/**
 * Base admin controller.
 */
abstract class AdminController
{
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    protected function render(string $view, array $data = []): void
    {
        $auth = $this->app->getService('auth');
        $settings = $this->app->getService('settings');
        
        $data['app'] = $this->app;
        $data['auth'] = $auth;
        $data['user'] = $auth->user();
        $data['settings'] = $settings;
        $data['csrf_field'] = CSRF::field();
        $data['csrf_token'] = CSRF::generate();
        $data['csrfToken'] = $data['csrf_token'];
        $data['siteName'] = $settings->get('site_title', 'GlassPress');
        $data['adminUrl'] = $this->app->getAdminUrl();
        $data['siteUrl'] = $this->app->getSiteUrl();
        $data['currentUri'] = $_SERVER['REQUEST_URI'] ?? '/';

        // Flash messages
        $data['flash'] = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);

        // Pending counts for sidebar badges
        $db = $this->app->getService('db');
        try {
            $data['pendingComments'] = $db->count('comments', "status = 'pending'");
        } catch (\Exception $e) {
            $data['pendingComments'] = 0;
        }

        View::display('admin.' . $view, $data, 'admin.layouts.main');
    }

    protected function redirect(string $url, string $message = '', string $type = 'success'): void
    {
        if ($message) {
            $_SESSION['_flash'] = ['message' => $message, 'type' => $type];
        }
        header('Location: ' . $url);
        exit;
    }

    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function requireCapability(string $capability): void
    {
        $auth = $this->app->getService('auth');
        if (!$auth->hasCapability($capability)) {
            http_response_code(403);
            echo '<h1>403 Forbidden</h1><p>You do not have permission to perform this action.</p>';
            exit;
        }
    }

    protected function sanitize(string $value): string
    {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    protected function sanitizeHtml(string $html): string
    {
        // Allow safe HTML tags for content
        $allowed = '<p><br><strong><em><b><i><u><s><a><img><h1><h2><h3><h4><h5><h6>'
            . '<ul><ol><li><blockquote><pre><code><table><thead><tbody><tr><th><td>'
            . '<hr><div><span><figure><figcaption><video><audio><source><iframe>'
            . '<sup><sub><mark><del><ins><details><summary><dl><dt><dd>';
        
        $clean = strip_tags($html, $allowed);
        
        // Remove event handlers and javascript: URLs
        $clean = preg_replace('/\s*on\w+\s*=\s*["\'][^"\']*["\']/i', '', $clean);
        $clean = preg_replace('/javascript\s*:/i', '', $clean);
        $clean = preg_replace('/data\s*:/i', '', $clean);
        $clean = preg_replace('/vbscript\s*:/i', '', $clean);
        
        return $clean;
    }

    protected function generateSlug(string $title, string $table = 'posts', ?int $excludeId = null): string
    {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');
        
        if (empty($slug)) {
            $slug = 'untitled';
        }

        // Check uniqueness
        $db = $this->app->getService('db');
        $baseSlug = $slug;
        $counter = 1;
        
        while (true) {
            $where = 'slug = ?';
            $params = [$slug];
            
            if ($excludeId !== null) {
                $where .= ' AND id != ?';
                $params[] = $excludeId;
            }
            
            if (!$db->exists($table, $where, $params)) {
                break;
            }
            
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
