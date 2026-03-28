<?php
namespace GlassPress\App\Controllers;

use GlassPress\Core\Application;
use GlassPress\Core\View;
use GlassPress\Core\Hooks;

class FrontendController
{
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Resolve a permalink-based URI to the correct content.
     */
    public function resolve(string $uri): void
    {
        $db = $this->app->getService('db');
        $settings = $this->app->getService('settings');

        // Check maintenance mode
        if ($settings->get('maintenance_mode') === '1') {
            $auth = $this->app->getService('auth');
            if (!$auth->check() || !$auth->hasCapability('manage_options')) {
                $this->renderMaintenance($settings);
                return;
            }
        }

        // Check redirects first
        $redirect = $db->fetch(sprintf(
            "SELECT * FROM %s WHERE source_url = ? AND is_active = 1",
            $db->prefix('redirects')
        ), [$uri]);

        if ($redirect) {
            $db->query(sprintf(
                "UPDATE %s SET hit_count = hit_count + 1 WHERE id = ?",
                $db->prefix('redirects')
            ), [$redirect['id']]);
            http_response_code((int) $redirect['status_code']);
            header('Location: ' . $redirect['target_url']);
            exit;
        }

        $categoryBase = $settings->get('category_base', 'category');
        $tagBase = $settings->get('tag_base', 'tag');

        // Category archive: /category/{slug}
        if (preg_match('#^/' . preg_quote($categoryBase, '#') . '/([^/]+)$#', $uri, $m)) {
            $this->archive('category', $m[1]);
            return;
        }

        // Tag archive: /tag/{slug}
        if (preg_match('#^/' . preg_quote($tagBase, '#') . '/([^/]+)$#', $uri, $m)) {
            $this->archive('tag', $m[1]);
            return;
        }

        // Author archive: /author/{slug}
        if (preg_match('#^/author/([^/]+)$#', $uri, $m)) {
            $this->authorArchive($m[1]);
            return;
        }

        // Date archives: /2025/01 or /2025
        if (preg_match('#^/(\d{4})(?:/(\d{2}))?$#', $uri, $m)) {
            $this->dateArchive($m[1], $m[2] ?? null);
            return;
        }

        // Page with pagination: /page/2
        if (preg_match('#^/page/(\d+)$#', $uri, $m)) {
            $this->home((int) $m[1]);
            return;
        }

        // Try to find a post or page by slug
        $slug = trim($uri, '/');
        if (empty($slug)) {
            $this->home();
            return;
        }

        // Check for post
        $post = $db->fetch(sprintf(
            "SELECT p.*, u.display_name as author_name, u.username as author_slug
             FROM %s p
             LEFT JOIN %s u ON u.id = p.author_id
             WHERE p.slug = ? AND p.status = 'publish'",
            $db->prefix('posts'),
            $db->prefix('users')
        ), [$slug]);

        if ($post) {
            if ($post['post_type'] === 'page') {
                $this->page($post);
            } else {
                $this->single($post);
            }
            return;
        }

        // Log 404 and show 404 page
        $this->log404($uri);
        $this->render404();
    }

    /**
     * Homepage
     */
    public function home(int $page = 1): void
    {
        $db = $this->app->getService('db');
        $settings = $this->app->getService('settings');
        $perPage = (int) $settings->get('posts_per_page', 10);
        $homepageType = $settings->get('homepage_type', 'posts');

        // Static homepage
        if ($homepageType === 'page') {
            $pageId = (int) $settings->get('homepage_id', 0);
            if ($pageId) {
                $staticPage = $db->fetch(sprintf(
                    "SELECT p.*, u.display_name as author_name FROM %s p LEFT JOIN %s u ON u.id = p.author_id WHERE p.id = ? AND p.status = 'publish'",
                    $db->prefix('posts'),
                    $db->prefix('users')
                ), [$pageId]);
                if ($staticPage) {
                    $this->page($staticPage);
                    return;
                }
            }
        }

        // Blog listing
        $offset = ($page - 1) * $perPage;
        $total = $db->count('posts', "post_type = 'post' AND status = 'publish'");
        $totalPages = ceil($total / $perPage);

        $posts = $db->fetchAll(sprintf(
            "SELECT p.*, u.display_name as author_name, u.username as author_slug,
                    m.file_path as featured_image
             FROM %s p
             LEFT JOIN %s u ON u.id = p.author_id
             LEFT JOIN %s m ON m.id = p.featured_image_id
             WHERE p.post_type = 'post' AND p.status = 'publish'
             ORDER BY p.is_sticky DESC, p.published_at DESC
             LIMIT %d OFFSET %d",
            $db->prefix('posts'),
            $db->prefix('users'),
            $db->prefix('media'),
            $perPage,
            $offset
        ));

        // Load categories for each post
        foreach ($posts as &$p) {
            $p['categories'] = $this->getPostTaxonomies($p['id'], 'category');
            $p['tags'] = $this->getPostTaxonomies($p['id'], 'tag');
        }
        unset($p);

        $this->render('index', [
            'posts' => $posts,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'pageTitle' => $page > 1 ? 'Page ' . $page : null,
        ]);
    }

    /**
     * Single post
     */
    public function single(array $post): void
    {
        $db = $this->app->getService('db');

        // Load taxonomies
        $post['categories'] = $this->getPostTaxonomies($post['id'], 'category');
        $post['tags'] = $this->getPostTaxonomies($post['id'], 'tag');

        // Load featured image
        if ($post['featured_image_id']) {
            $img = $db->fetch(sprintf(
                "SELECT * FROM %s WHERE id = ?",
                $db->prefix('media')
            ), [$post['featured_image_id']]);
            $post['featured_image'] = $img['file_path'] ?? null;
            $post['featured_image_alt'] = $img['alt_text'] ?? '';
        }

        // Load SEO meta
        $post['seo'] = $db->fetch(sprintf(
            "SELECT * FROM %s WHERE object_id = ? AND object_type = 'post'",
            $db->prefix('seo_meta')
        ), [$post['id']]) ?: [];

        // Load approved comments
        $comments = $db->fetchAll(sprintf(
            "SELECT * FROM %s WHERE post_id = ? AND status = 'approved' ORDER BY created_at ASC",
            $db->prefix('comments')
        ), [$post['id']]);

        // Build comment tree (threaded)
        $commentTree = $this->buildCommentTree($comments);

        // Related posts (same categories)
        $relatedPosts = [];
        $settings = $this->app->getService('settings');
        if ($settings->get('show_related_posts', '1') === '1' && !empty($post['categories'])) {
            $catIds = array_column($post['categories'], 'id');
            $placeholders = implode(',', array_fill(0, count($catIds), '?'));
            $relatedPosts = $db->fetchAll(sprintf(
                "SELECT DISTINCT p.id, p.title, p.slug, p.excerpt, p.published_at,
                        m.file_path as featured_image
                 FROM %s p
                 JOIN %s pt ON pt.post_id = p.id
                 LEFT JOIN %s m ON m.id = p.featured_image_id
                 WHERE pt.taxonomy_id IN ({$placeholders})
                 AND p.id != ? AND p.status = 'publish' AND p.post_type = 'post'
                 ORDER BY p.published_at DESC LIMIT 3",
                $db->prefix('posts'),
                $db->prefix('post_taxonomy'),
                $db->prefix('media')
            ), [...$catIds, $post['id']]);
        }

        // Author info
        $author = $db->fetch(sprintf(
            "SELECT id, username, display_name, email, bio FROM %s WHERE id = ?",
            $db->prefix('users')
        ), [$post['author_id']]);

        $this->render('single', [
            'post' => $post,
            'comments' => $commentTree,
            'commentCount' => count($comments),
            'relatedPosts' => $relatedPosts,
            'author' => $author,
            'pageTitle' => $post['title'],
        ]);
    }

    /**
     * Single page
     */
    public function page(array $page): void
    {
        $db = $this->app->getService('db');

        // Featured image
        if (!empty($page['featured_image_id'])) {
            $img = $db->fetch(sprintf("SELECT * FROM %s WHERE id = ?", $db->prefix('media')), [$page['featured_image_id']]);
            $page['featured_image'] = $img['file_path'] ?? null;
        }

        // SEO meta
        $page['seo'] = $db->fetch(sprintf(
            "SELECT * FROM %s WHERE object_id = ? AND object_type = 'post'",
            $db->prefix('seo_meta')
        ), [$page['id']]) ?: [];

        // Determine template
        $template = $page['page_template'] ?? 'default';

        // Load comments if enabled
        $comments = [];
        $commentCount = 0;
        if ($page['comment_status'] === 'open') {
            $allComments = $db->fetchAll(sprintf(
                "SELECT * FROM %s WHERE post_id = ? AND status = 'approved' ORDER BY created_at ASC",
                $db->prefix('comments')
            ), [$page['id']]);
            $comments = $this->buildCommentTree($allComments);
            $commentCount = count($allComments);
        }

        $viewName = $template !== 'default' ? "page-{$template}" : 'page';

        $this->render($viewName, [
            'page' => $page,
            'comments' => $comments,
            'commentCount' => $commentCount,
            'pageTitle' => $page['title'],
        ]);
    }

    /**
     * Category/tag archive
     */
    public function archive(string $type, string $slug): void
    {
        $db = $this->app->getService('db');
        $settings = $this->app->getService('settings');
        $perPage = (int) $settings->get('posts_per_page', 10);
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $taxonomy = $db->fetch(sprintf(
            "SELECT * FROM %s WHERE slug = ? AND taxonomy = ?",
            $db->prefix('taxonomies')
        ), [$slug, $type]);

        if (!$taxonomy) {
            $this->log404("/{$type}/{$slug}");
            $this->render404();
            return;
        }

        $offset = ($page - 1) * $perPage;
        $total = $db->fetch(sprintf(
            "SELECT COUNT(*) as cnt FROM %s pt
             JOIN %s p ON p.id = pt.post_id
             WHERE pt.taxonomy_id = ? AND p.status = 'publish' AND p.post_type = 'post'",
            $db->prefix('post_taxonomy'),
            $db->prefix('posts')
        ), [$taxonomy['id']]);
        $totalCount = (int) ($total['cnt'] ?? 0);
        $totalPages = ceil($totalCount / $perPage);

        $posts = $db->fetchAll(sprintf(
            "SELECT p.*, u.display_name as author_name, u.username as author_slug,
                    m.file_path as featured_image
             FROM %s p
             JOIN %s pt ON pt.post_id = p.id
             LEFT JOIN %s u ON u.id = p.author_id
             LEFT JOIN %s m ON m.id = p.featured_image_id
             WHERE pt.taxonomy_id = ? AND p.status = 'publish' AND p.post_type = 'post'
             ORDER BY p.published_at DESC
             LIMIT %d OFFSET %d",
            $db->prefix('posts'),
            $db->prefix('post_taxonomy'),
            $db->prefix('users'),
            $db->prefix('media'),
            $perPage,
            $offset
        ), [$taxonomy['id']]);

        $this->render('archive', [
            'taxonomy' => $taxonomy,
            'posts' => $posts,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $totalCount,
            'archiveType' => $type,
            'pageTitle' => $taxonomy['name'],
        ]);
    }

    /**
     * Author archive
     */
    public function authorArchive(string $slug): void
    {
        $db = $this->app->getService('db');
        $settings = $this->app->getService('settings');
        $perPage = (int) $settings->get('posts_per_page', 10);
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $author = $db->fetch(sprintf(
            "SELECT id, username, display_name, bio FROM %s WHERE username = ?",
            $db->prefix('users')
        ), [$slug]);

        if (!$author) {
            $this->log404("/author/{$slug}");
            $this->render404();
            return;
        }

        $offset = ($page - 1) * $perPage;
        $total = $db->count('posts', "author_id = ? AND status = 'publish' AND post_type = 'post'", [$author['id']]);
        $totalPages = ceil($total / $perPage);

        $posts = $db->fetchAll(sprintf(
            "SELECT p.*, m.file_path as featured_image
             FROM %s p
             LEFT JOIN %s m ON m.id = p.featured_image_id
             WHERE p.author_id = ? AND p.status = 'publish' AND p.post_type = 'post'
             ORDER BY p.published_at DESC LIMIT %d OFFSET %d",
            $db->prefix('posts'),
            $db->prefix('media'),
            $perPage,
            $offset
        ), [$author['id']]);

        $this->render('archive', [
            'author' => $author,
            'posts' => $posts,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'archiveType' => 'author',
            'pageTitle' => $author['display_name'],
        ]);
    }

    /**
     * Date archive
     */
    public function dateArchive(string $year, ?string $month): void
    {
        $db = $this->app->getService('db');
        $settings = $this->app->getService('settings');
        $perPage = (int) $settings->get('posts_per_page', 10);
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $where = "YEAR(p.published_at) = ? AND p.status = 'publish' AND p.post_type = 'post'";
        $params = [(int) $year];

        if ($month) {
            $where .= " AND MONTH(p.published_at) = ?";
            $params[] = (int) $month;
        }

        $offset = ($page - 1) * $perPage;

        $total = $db->fetch(sprintf(
            "SELECT COUNT(*) as cnt FROM %s p WHERE {$where}",
            $db->prefix('posts')
        ), $params);
        $totalCount = (int) ($total['cnt'] ?? 0);
        $totalPages = ceil($totalCount / $perPage);

        $posts = $db->fetchAll(sprintf(
            "SELECT p.*, u.display_name as author_name, m.file_path as featured_image
             FROM %s p
             LEFT JOIN %s u ON u.id = p.author_id
             LEFT JOIN %s m ON m.id = p.featured_image_id
             WHERE {$where}
             ORDER BY p.published_at DESC LIMIT %d OFFSET %d",
            $db->prefix('posts'),
            $db->prefix('users'),
            $db->prefix('media'),
            $perPage,
            $offset
        ), $params);

        $label = $month ? date('F', mktime(0, 0, 0, (int) $month)) . " {$year}" : $year;

        $this->render('archive', [
            'posts' => $posts,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $totalCount,
            'archiveType' => 'date',
            'pageTitle' => $label,
        ]);
    }

    /**
     * Search results
     */
    public function search(): void
    {
        $db = $this->app->getService('db');
        $settings = $this->app->getService('settings');
        $perPage = (int) $settings->get('posts_per_page', 10);
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $query = trim($_GET['q'] ?? '');

        if (empty($query)) {
            $this->render('search', [
                'posts' => [],
                'query' => '',
                'page' => 1,
                'totalPages' => 0,
                'total' => 0,
                'pageTitle' => 'Search',
            ]);
            return;
        }

        $searchTerm = '%' . $query . '%';
        $offset = ($page - 1) * $perPage;

        $total = $db->fetch(sprintf(
            "SELECT COUNT(*) as cnt FROM %s WHERE status = 'publish' AND (title LIKE ? OR content LIKE ?)",
            $db->prefix('posts')
        ), [$searchTerm, $searchTerm]);
        $totalCount = (int) ($total['cnt'] ?? 0);
        $totalPages = ceil($totalCount / $perPage);

        $posts = $db->fetchAll(sprintf(
            "SELECT p.*, u.display_name as author_name, m.file_path as featured_image
             FROM %s p
             LEFT JOIN %s u ON u.id = p.author_id
             LEFT JOIN %s m ON m.id = p.featured_image_id
             WHERE p.status = 'publish' AND (p.title LIKE ? OR p.content LIKE ?)
             ORDER BY p.published_at DESC LIMIT %d OFFSET %d",
            $db->prefix('posts'),
            $db->prefix('users'),
            $db->prefix('media'),
            $perPage,
            $offset
        ), [$searchTerm, $searchTerm]);

        $this->render('search', [
            'posts' => $posts,
            'query' => htmlspecialchars($query, ENT_QUOTES, 'UTF-8'),
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $totalCount,
            'pageTitle' => "Search: {$query}",
        ]);
    }

    /**
     * Render a theme template
     */
    protected function render(string $template, array $data = []): void
    {
        $settings = $this->app->getService('settings');
        $db = $this->app->getService('db');

        // Common data
        $data['app'] = $this->app;
        $data['settings'] = $settings;
        $data['siteUrl'] = $this->app->getSiteUrl();
        $data['siteName'] = $settings->get('site_title', 'GlassPress');
        $data['siteTagline'] = $settings->get('site_tagline', '');
        $data['dateFormat'] = $settings->get('date_format', 'F j, Y');

        // Load primary menu
        $menu = $db->fetch(sprintf(
            "SELECT * FROM %s WHERE location = 'primary' OR id = (SELECT MIN(id) FROM %s) LIMIT 1",
            $db->prefix('menus'),
            $db->prefix('menus')
        ));
        $data['menuItems'] = [];
        if ($menu) {
            $data['menuItems'] = $db->fetchAll(sprintf(
                "SELECT * FROM %s WHERE menu_id = ? ORDER BY sort_order ASC",
                $db->prefix('menu_items')
            ), [$menu['id']]);
        }

        // Sidebar widgets data
        $data['recentPosts'] = $db->fetchAll(sprintf(
            "SELECT id, title, slug, published_at FROM %s WHERE status = 'publish' AND post_type = 'post' ORDER BY published_at DESC LIMIT 5",
            $db->prefix('posts')
        ));
        $data['categories'] = $db->fetchAll(sprintf(
            "SELECT * FROM %s WHERE taxonomy = 'category' ORDER BY name ASC",
            $db->prefix('taxonomies')
        ));
        $data['tags'] = $db->fetchAll(sprintf(
            "SELECT * FROM %s WHERE taxonomy = 'tag' AND count > 0 ORDER BY count DESC LIMIT 20",
            $db->prefix('taxonomies')
        ));

        // SEO data
        $data['seo'] = $this->buildSeoData($data);

        // Hooks
        $hooks = $this->app->getService('hooks');
        $hooks->doAction('before_render', $template, $data);

        View::display('theme.' . $template, $data, 'theme.layout');
    }

    /**
     * Build SEO meta data for head output
     */
    protected function buildSeoData(array $data): array
    {
        $settings = $this->app->getService('settings');
        $seo = [];

        $separator = $settings->get('seo_title_separator', '|');
        $siteName = $settings->get('site_title', 'GlassPress');

        // Title
        if (isset($data['pageTitle']) && $data['pageTitle']) {
            $seo['title'] = $data['pageTitle'] . " {$separator} {$siteName}";
        } else {
            $homepageTitle = $settings->get('seo_homepage_title', '');
            $seo['title'] = $homepageTitle ?: "{$siteName} {$separator} " . $settings->get('site_tagline', '');
        }

        // Description
        if (isset($data['post']['seo']['meta_description']) && $data['post']['seo']['meta_description']) {
            $seo['description'] = $data['post']['seo']['meta_description'];
        } elseif (isset($data['post']['excerpt']) && $data['post']['excerpt']) {
            $seo['description'] = mb_substr(strip_tags($data['post']['excerpt']), 0, 160);
        } else {
            $seo['description'] = $settings->get('seo_homepage_description', $settings->get('site_tagline', ''));
        }

        // Canonical
        if (isset($data['post']['seo']['canonical_url']) && $data['post']['seo']['canonical_url']) {
            $seo['canonical'] = $data['post']['seo']['canonical_url'];
        } elseif (isset($data['post']['slug'])) {
            $seo['canonical'] = $this->app->getSiteUrl($data['post']['slug']);
        } else {
            $seo['canonical'] = $this->app->getSiteUrl();
        }

        // Robots
        $seo['robots'] = 'index, follow';
        if (isset($data['post']['seo']['robots']) && $data['post']['seo']['robots']) {
            $seo['robots'] = $data['post']['seo']['robots'];
        }

        // Open Graph
        if ($settings->get('enable_opengraph', '1') === '1') {
            $seo['og_title'] = $data['post']['seo']['og_title'] ?? $seo['title'];
            $seo['og_description'] = $data['post']['seo']['og_description'] ?? $seo['description'];
            $seo['og_type'] = isset($data['post']) ? 'article' : 'website';
            $seo['og_url'] = $seo['canonical'];
            $seo['og_site_name'] = $siteName;
            if (isset($data['post']['seo']['og_image']) && $data['post']['seo']['og_image']) {
                $seo['og_image'] = $data['post']['seo']['og_image'];
            } elseif (isset($data['post']['featured_image']) && $data['post']['featured_image']) {
                $seo['og_image'] = $this->app->getSiteUrl($data['post']['featured_image']);
            }
        }

        // Twitter Cards
        if ($settings->get('enable_twitter_cards', '1') === '1') {
            $seo['twitter_card'] = 'summary_large_image';
            $seo['twitter_title'] = $seo['og_title'] ?? $seo['title'];
            $seo['twitter_description'] = $seo['og_description'] ?? $seo['description'];
            if (isset($seo['og_image'])) {
                $seo['twitter_image'] = $seo['og_image'];
            }
            $handle = $settings->get('twitter_handle', '');
            if ($handle) {
                $seo['twitter_site'] = $handle;
            }
        }

        // JSON-LD structured data
        if ($settings->get('enable_schema', '1') === '1') {
            $seo['schema'] = $this->buildSchemaData($data);
        }

        return $seo;
    }

    protected function buildSchemaData(array $data): string
    {
        $settings = $this->app->getService('settings');
        $siteName = $settings->get('site_title', 'GlassPress');
        $siteUrl = $this->app->getSiteUrl();

        if (isset($data['post']) && $data['post']['post_type'] === 'post') {
            $schema = [
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => $data['post']['title'],
                'url' => $siteUrl . '/' . $data['post']['slug'],
                'datePublished' => $data['post']['published_at'] ?? $data['post']['created_at'],
                'dateModified' => $data['post']['updated_at'] ?? $data['post']['created_at'],
                'author' => [
                    '@type' => 'Person',
                    'name' => $data['post']['author_name'] ?? 'Unknown',
                ],
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => $siteName,
                ],
            ];
            if (isset($data['post']['featured_image'])) {
                $schema['image'] = $siteUrl . '/' . $data['post']['featured_image'];
            }
            if (isset($data['post']['excerpt']) && $data['post']['excerpt']) {
                $schema['description'] = strip_tags($data['post']['excerpt']);
            }
        } else {
            $schema = [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => $siteName,
                'url' => $siteUrl,
            ];
            if ($settings->get('site_tagline')) {
                $schema['description'] = $settings->get('site_tagline');
            }
        }

        return json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get taxonomies for a post
     */
    protected function getPostTaxonomies(int $postId, string $type): array
    {
        $db = $this->app->getService('db');
        return $db->fetchAll(sprintf(
            "SELECT t.* FROM %s t JOIN %s pt ON pt.taxonomy_id = t.id WHERE pt.post_id = ? AND t.taxonomy = ?",
            $db->prefix('taxonomies'),
            $db->prefix('post_taxonomy')
        ), [$postId, $type]);
    }

    /**
     * Build threaded comment tree
     */
    protected function buildCommentTree(array $comments, int $parentId = 0): array
    {
        $tree = [];
        foreach ($comments as $comment) {
            if ((int) $comment['parent_id'] === $parentId) {
                $comment['children'] = $this->buildCommentTree($comments, (int) $comment['id']);
                $tree[] = $comment;
            }
        }
        return $tree;
    }

    /**
     * Log a 404 hit
     */
    protected function log404(string $url): void
    {
        try {
            $db = $this->app->getService('db');
            $existing = $db->fetch(sprintf(
                "SELECT id FROM %s WHERE url = ?",
                $db->prefix('log_404')
            ), [$url]);

            if ($existing) {
                $db->query(sprintf(
                    "UPDATE %s SET hit_count = hit_count + 1, last_hit = NOW() WHERE id = ?",
                    $db->prefix('log_404')
                ), [$existing['id']]);
            } else {
                $db->insert('log_404', [
                    'url' => mb_substr($url, 0, 500),
                    'referrer' => mb_substr($_SERVER['HTTP_REFERER'] ?? '', 0, 500) ?: null,
                    'user_agent' => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255) ?: null,
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'first_hit' => date('Y-m-d H:i:s'),
                    'last_hit' => date('Y-m-d H:i:s'),
                ]);
            }
        } catch (\Exception $e) {
            // Don't break the page for logging failures
        }
    }

    /**
     * Render 404 page
     */
    protected function render404(): void
    {
        http_response_code(404);
        $this->render('404', ['pageTitle' => 'Page Not Found']);
    }

    /**
     * Render maintenance page
     */
    protected function renderMaintenance(\GlassPress\Core\Settings $settings): void
    {
        http_response_code(503);
        header('Retry-After: 3600');
        $message = $settings->get('maintenance_message', 'We are currently performing scheduled maintenance. Please check back soon.');
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Maintenance</title>';
        echo '<style>body{font-family:system-ui;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;background:#0a0a1a;color:#fff}';
        echo '.box{text-align:center;padding:60px;max-width:500px}.box h1{font-size:2em;margin-bottom:16px}.box p{color:rgba(255,255,255,.6);line-height:1.6}</style>';
        echo '</head><body><div class="box"><h1>Under Maintenance</h1><p>' . htmlspecialchars($message) . '</p></div></body></html>';
        exit;
    }
}
