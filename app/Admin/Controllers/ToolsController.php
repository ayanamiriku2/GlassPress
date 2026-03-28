<?php
namespace GlassPress\App\Admin\Controllers;

class ToolsController extends AdminController
{
    public function index(): void
    {
        $this->requireCapability('manage_options');
        $this->render('tools.index', ['pageTitle' => 'Tools']);
    }

    public function seoHealth(): void
    {
        $this->requireCapability('manage_options');
        $db = $this->app->getService('db');

        $issues = [];

        // Posts without meta description
        $noMeta = $db->fetchAll(sprintf(
            "SELECT p.id, p.title, p.post_type FROM %s p
             LEFT JOIN %s s ON s.object_id = p.id AND s.object_type = 'post'
             WHERE p.status = 'publish'
             AND (s.seo_description IS NULL OR s.seo_description = '')",
            $db->prefix('posts'),
            $db->prefix('seo_meta')
        ));
        foreach ($noMeta as $row) {
            $issues[] = [
                'type' => 'warning',
                'category' => 'Missing Meta Description',
                'message' => htmlspecialchars($row['title']),
                'link' => $this->app->getAdminUrl("posts/edit/{$row['id']}"),
            ];
        }

        // Posts without featured image
        $noImage = $db->fetchAll(sprintf(
            "SELECT id, title FROM %s WHERE status = 'publish' AND featured_image_id IS NULL AND post_type = 'post'",
            $db->prefix('posts')
        ));
        foreach ($noImage as $row) {
            $issues[] = [
                'type' => 'info',
                'category' => 'No Featured Image',
                'message' => htmlspecialchars($row['title']),
                'link' => $this->app->getAdminUrl("posts/edit/{$row['id']}"),
            ];
        }

        // Posts with short titles (< 20 chars)
        $shortTitles = $db->fetchAll(sprintf(
            "SELECT id, title FROM %s WHERE status = 'publish' AND CHAR_LENGTH(title) < 20",
            $db->prefix('posts')
        ));
        foreach ($shortTitles as $row) {
            $issues[] = [
                'type' => 'info',
                'category' => 'Short Title',
                'message' => htmlspecialchars($row['title']),
                'link' => $this->app->getAdminUrl("posts/edit/{$row['id']}"),
            ];
        }

        // Posts with very long titles (> 70 chars for SEO)
        $longTitles = $db->fetchAll(sprintf(
            "SELECT id, title FROM %s WHERE status = 'publish' AND CHAR_LENGTH(title) > 70",
            $db->prefix('posts')
        ));
        foreach ($longTitles as $row) {
            $issues[] = [
                'type' => 'warning',
                'category' => 'Long Title (70+ chars)',
                'message' => htmlspecialchars($row['title']),
                'link' => $this->app->getAdminUrl("posts/edit/{$row['id']}"),
            ];
        }

        // Check if site title is set
        $settings = $this->app->getService('settings');
        if (empty($settings->get('site_title'))) {
            $issues[] = [
                'type' => 'error',
                'category' => 'Site Configuration',
                'message' => 'Site title is not set',
                'link' => $this->app->getAdminUrl('settings/general'),
            ];
        }

        // Check if meta description is set
        if (empty($settings->get('meta_description'))) {
            $issues[] = [
                'type' => 'warning',
                'category' => 'Site Configuration',
                'message' => 'Homepage meta description is not set',
                'link' => $this->app->getAdminUrl('settings/seo'),
            ];
        }

        $this->render('tools.seo_health', [
            'pageTitle' => 'SEO Health Check',
            'issues' => $issues,
            'totalPublished' => $db->count('posts', "status = 'publish'"),
        ]);
    }

    public function log404(): void
    {
        $this->requireCapability('manage_options');
        $db = $this->app->getService('db');

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 30;
        $offset = ($page - 1) * $perPage;

        $total = $db->count('log_404');
        $entries = $db->fetchAll(sprintf(
            "SELECT * FROM %s ORDER BY hit_count DESC, last_hit DESC LIMIT %d OFFSET %d",
            $db->prefix('log_404'),
            $perPage,
            $offset
        ));

        $this->render('tools.log_404', [
            'pageTitle' => '404 Error Log',
            'entries' => $entries,
            'total' => $total,
            'page' => $page,
            'totalPages' => ceil($total / $perPage),
        ]);
    }

    public function clearCache(): void
    {
        $this->requireCapability('manage_options');

        $cache = $this->app->getService('cache');
        $cache->flush();

        // Also clear any compiled views if they exist
        $cacheDir = GLASSPRESS_ROOT . '/cache';
        if (is_dir($cacheDir)) {
            $files = glob($cacheDir . '/*.php');
            if ($files) {
                foreach ($files as $file) {
                    @unlink($file);
                }
            }
        }

        $this->redirect($this->app->getAdminUrl('tools'), 'Cache cleared successfully.');
    }

    public function export(): void
    {
        $this->requireCapability('manage_options');
        $db = $this->app->getService('db');

        $data = [
            'glasspress_export' => true,
            'version' => '1.0.0',
            'exported_at' => date('c'),
            'posts' => $db->fetchAll(sprintf("SELECT * FROM %s", $db->prefix('posts'))),
            'taxonomies' => $db->fetchAll(sprintf("SELECT * FROM %s", $db->prefix('taxonomies'))),
            'post_taxonomy' => $db->fetchAll(sprintf("SELECT * FROM %s", $db->prefix('post_taxonomy'))),
            'comments' => $db->fetchAll(sprintf("SELECT * FROM %s", $db->prefix('comments'))),
            'menus' => $db->fetchAll(sprintf("SELECT * FROM %s", $db->prefix('menus'))),
            'menu_items' => $db->fetchAll(sprintf("SELECT * FROM %s", $db->prefix('menu_items'))),
            'seo_meta' => $db->fetchAll(sprintf("SELECT * FROM %s", $db->prefix('seo_meta'))),
            'redirects' => $db->fetchAll(sprintf("SELECT * FROM %s", $db->prefix('redirects'))),
        ];

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="glasspress-export-' . date('Y-m-d') . '.json"');
        header('Content-Length: ' . strlen($json));
        echo $json;
        exit;
    }

    public function import(): void
    {
        $this->requireCapability('manage_options');

        if (empty($_FILES['import_file']['tmp_name'])) {
            $this->redirect($this->app->getAdminUrl('tools'), 'No file uploaded.', 'error');
            return;
        }

        $file = $_FILES['import_file']['tmp_name'];
        $contents = file_get_contents($file);
        $data = json_decode($contents, true);

        if (!$data || empty($data['glasspress_export'])) {
            $this->redirect($this->app->getAdminUrl('tools'), 'Invalid export file.', 'error');
            return;
        }

        $db = $this->app->getService('db');
        $imported = 0;

        // Import posts
        if (!empty($data['posts'])) {
            foreach ($data['posts'] as $post) {
                // Check if slug already exists
                if ($db->exists('posts', 'slug = ?', [$post['slug']])) {
                    continue;
                }
                $id = $post['id'];
                unset($post['id']);
                $db->insert('posts', $post);
                $imported++;
            }
        }

        // Import taxonomies
        if (!empty($data['taxonomies'])) {
            foreach ($data['taxonomies'] as $tax) {
                if ($db->exists('taxonomies', 'slug = ? AND taxonomy = ?', [$tax['slug'], $tax['taxonomy']])) {
                    continue;
                }
                unset($tax['id']);
                $db->insert('taxonomies', $tax);
            }
        }

        // Import redirects
        if (!empty($data['redirects'])) {
            foreach ($data['redirects'] as $redirect) {
                if ($db->exists('redirects', 'source_url = ?', [$redirect['source_url']])) {
                    continue;
                }
                unset($redirect['id']);
                $db->insert('redirects', $redirect);
            }
        }

        $this->redirect($this->app->getAdminUrl('tools'), "Import completed. {$imported} posts imported.");
    }

    public function systemInfo(): void
    {
        $this->requireCapability('manage_options');
        $db = $this->app->getService('db');

        $info = [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'mysql_version' => '',
            'max_upload' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time') . 's',
            'php_extensions' => implode(', ', get_loaded_extensions()),
            'gd_support' => extension_loaded('gd') ? 'Yes' : 'No',
            'curl_support' => extension_loaded('curl') ? 'Yes' : 'No',
            'json_support' => extension_loaded('json') ? 'Yes' : 'No',
            'mbstring_support' => extension_loaded('mbstring') ? 'Yes' : 'No',
            'openssl_support' => extension_loaded('openssl') ? 'Yes' : 'No',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? '',
            'glasspress_root' => GLASSPRESS_ROOT,
            'disk_free' => function_exists('disk_free_space') ? $this->formatBytes(disk_free_space(GLASSPRESS_ROOT)) : 'N/A',
        ];

        try {
            $row = $db->fetch("SELECT VERSION() as v");
            $info['mysql_version'] = $row['v'] ?? 'Unknown';
        } catch (\Exception $e) {
            $info['mysql_version'] = 'Error: ' . $e->getMessage();
        }

        // Content stats
        $stats = [
            'posts' => $db->count('posts', "post_type = 'post'"),
            'pages' => $db->count('posts', "post_type = 'page'"),
            'comments' => $db->count('comments'),
            'media' => $db->count('media'),
            'users' => $db->count('users'),
            'categories' => $db->count('taxonomies', "taxonomy = 'category'"),
            'tags' => $db->count('taxonomies', "taxonomy = 'tag'"),
        ];

        $this->render('tools.system_info', [
            'pageTitle' => 'System Information',
            'info' => $info,
            'stats' => $stats,
        ]);
    }

    private function formatBytes(float $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
