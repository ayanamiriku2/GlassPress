<?php
namespace GlassPress\App\Admin\Controllers;

class PageController extends AdminController
{
    public function index(): void
    {
        $db = $this->app->getService('db');
        $status = $_GET['status'] ?? '';
        $search = trim($_GET['s'] ?? '');
        $page = max(1, (int) ($_GET['paged'] ?? 1));
        $perPage = 20;

        $where = "p.post_type = 'page'";
        $params = [];

        if ($status && in_array($status, ['publish', 'draft', 'pending', 'trash', 'private'])) {
            $where .= ' AND p.status = ?';
            $params[] = $status;
        } else {
            $where .= " AND p.status != 'trash'";
        }

        if ($search) {
            $where .= ' AND (p.title LIKE ? OR p.content LIKE ?)';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }

        $total = (int) $db->fetchColumn(
            sprintf('SELECT COUNT(*) FROM %s p WHERE %s', $db->prefix('posts'), $where),
            $params
        );

        $posts = $db->fetchAll(sprintf(
            "SELECT p.*, u.display_name as author_name FROM %s p 
             LEFT JOIN %s u ON p.author_id = u.id 
             WHERE %s ORDER BY p.menu_order ASC, p.created_at DESC LIMIT %d OFFSET %d",
            $db->prefix('posts'), $db->prefix('users'), $where, $perPage, ($page - 1) * $perPage
        ), $params);

        $counts = [];
        $countRows = $db->fetchAll(sprintf(
            "SELECT status, COUNT(*) as cnt FROM %s WHERE post_type = 'page' GROUP BY status",
            $db->prefix('posts')
        ));
        foreach ($countRows as $row) {
            $counts[$row['status']] = (int) $row['cnt'];
        }

        $this->render('pages.index', [
            'pageTitle' => 'Pages',
            'posts' => $posts,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'pages' => ceil($total / $perPage),
            'status' => $status,
            'search' => $search,
            'counts' => $counts,
        ]);
    }

    public function create(): void
    {
        $this->requireCapability('create_posts');
        $db = $this->app->getService('db');

        $parentPages = $db->fetchAll(sprintf(
            "SELECT id, title FROM %s WHERE post_type = 'page' AND status != 'trash' AND parent_id IS NULL ORDER BY title",
            $db->prefix('posts')
        ));

        $authors = $db->fetchAll(sprintf(
            "SELECT id, display_name, username FROM %s WHERE status = 'active' ORDER BY display_name",
            $db->prefix('users')
        ));

        $seoMeta = null;

        $this->render('pages.editor', [
            'pageTitle' => 'Add New Page',
            'post' => null,
            'parentPages' => $parentPages,
            'authors' => $authors,
            'seoMeta' => $seoMeta,
            'isNew' => true,
        ]);
    }

    public function store(): void
    {
        $this->requireCapability('create_posts');
        $db = $this->app->getService('db');
        $auth = $this->app->getService('auth');

        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '') ?: $title;
        $slug = $this->generateSlug($slug, 'posts');
        $content = $this->sanitizeHtml($_POST['content'] ?? '');
        $excerpt = $this->sanitize($_POST['excerpt'] ?? '');
        $status = $_POST['status'] ?? 'draft';
        $commentStatus = $_POST['comment_status'] ?? 'closed';
        $parentId = (int) ($_POST['parent_id'] ?? 0) ?: null;
        $menuOrder = (int) ($_POST['menu_order'] ?? 0);
        $pageTemplate = $this->sanitize($_POST['page_template'] ?? 'default');
        $featuredImageId = (int) ($_POST['featured_image_id'] ?? 0) ?: null;
        $authorId = (int) ($_POST['author_id'] ?? $auth->userId());

        $publishedAt = null;
        if ($status === 'publish') {
            $publishedAt = date('Y-m-d H:i:s');
        }

        if (in_array($status, ['publish']) && !$auth->hasCapability('publish_posts')) {
            $status = 'pending';
        }

        $postId = $db->insert('posts', [
            'author_id' => $authorId,
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'excerpt' => $excerpt,
            'status' => $status,
            'post_type' => 'page',
            'comment_status' => $commentStatus,
            'parent_id' => $parentId,
            'menu_order' => $menuOrder,
            'page_template' => $pageTemplate,
            'featured_image_id' => $featuredImageId,
            'published_at' => $publishedAt,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->saveSeoMeta($db, 'post', $postId);

        $this->redirect($this->app->getAdminUrl("pages/edit/{$postId}"), 'Page created successfully.');
    }

    public function edit(string $id): void
    {
        $db = $this->app->getService('db');

        $post = $db->fetch(
            sprintf("SELECT * FROM %s WHERE id = ? AND post_type = 'page'", $db->prefix('posts')),
            [(int) $id]
        );

        if (!$post) {
            http_response_code(404);
            echo '<h1>Page not found</h1>';
            return;
        }

        $parentPages = $db->fetchAll(sprintf(
            "SELECT id, title FROM %s WHERE post_type = 'page' AND status != 'trash' AND id != ? AND parent_id IS NULL ORDER BY title",
            $db->prefix('posts')
        ), [(int) $id]);

        $authors = $db->fetchAll(sprintf(
            "SELECT id, display_name, username FROM %s WHERE status = 'active' ORDER BY display_name",
            $db->prefix('users')
        ));

        $seoMeta = $db->fetch(
            sprintf("SELECT * FROM %s WHERE object_type = 'post' AND object_id = ?", $db->prefix('seo_meta')),
            [(int) $id]
        );

        $this->render('pages.editor', [
            'pageTitle' => 'Edit Page',
            'post' => $post,
            'parentPages' => $parentPages,
            'authors' => $authors,
            'seoMeta' => $seoMeta,
            'isNew' => false,
        ]);
    }

    public function update(string $id): void
    {
        $db = $this->app->getService('db');
        $postId = (int) $id;

        $post = $db->fetch(
            sprintf("SELECT * FROM %s WHERE id = ? AND post_type = 'page'", $db->prefix('posts')),
            [$postId]
        );

        if (!$post) {
            $this->redirect($this->app->getAdminUrl('pages'), 'Page not found.', 'error');
            return;
        }

        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '') ?: $title;
        $slug = $this->generateSlug($slug, 'posts', $postId);
        $content = $this->sanitizeHtml($_POST['content'] ?? '');
        $excerpt = $this->sanitize($_POST['excerpt'] ?? '');
        $status = $_POST['status'] ?? $post['status'];
        $commentStatus = $_POST['comment_status'] ?? 'closed';
        $parentId = (int) ($_POST['parent_id'] ?? 0) ?: null;
        $menuOrder = (int) ($_POST['menu_order'] ?? 0);
        $pageTemplate = $this->sanitize($_POST['page_template'] ?? 'default');
        $featuredImageId = (int) ($_POST['featured_image_id'] ?? 0) ?: null;
        $authorId = (int) ($_POST['author_id'] ?? $post['author_id']);
        $publishedAt = $post['published_at'];

        if ($status === 'publish' && !$publishedAt) {
            $publishedAt = date('Y-m-d H:i:s');
        }

        $db->update('posts', [
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'excerpt' => $excerpt,
            'status' => $status,
            'comment_status' => $commentStatus,
            'parent_id' => $parentId,
            'menu_order' => $menuOrder,
            'page_template' => $pageTemplate,
            'featured_image_id' => $featuredImageId,
            'author_id' => $authorId,
            'published_at' => $publishedAt,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$postId]);

        $this->saveSeoMeta($db, 'post', $postId);

        $this->redirect($this->app->getAdminUrl("pages/edit/{$postId}"), 'Page updated successfully.');
    }

    public function delete(string $id): void
    {
        $db = $this->app->getService('db');
        $postId = (int) $id;

        $post = $db->fetch(
            sprintf("SELECT * FROM %s WHERE id = ? AND post_type = 'page'", $db->prefix('posts')),
            [$postId]
        );

        if (!$post) {
            $this->redirect($this->app->getAdminUrl('pages'), 'Page not found.', 'error');
            return;
        }

        if ($post['status'] === 'trash') {
            $db->delete('seo_meta', "object_type = 'post' AND object_id = ?", [$postId]);
            $db->delete('comments', 'post_id = ?', [$postId]);
            $db->delete('posts', 'id = ?', [$postId]);
            $this->redirect($this->app->getAdminUrl('pages?status=trash'), 'Page permanently deleted.');
        } else {
            $db->update('posts', ['status' => 'trash', 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$postId]);
            $this->redirect($this->app->getAdminUrl('pages'), 'Page moved to trash.');
        }
    }

    private function saveSeoMeta($db, string $type, int $objectId): void
    {
        $seoTitle = trim($_POST['seo_title'] ?? '');
        $seoDesc = trim($_POST['seo_description'] ?? '');
        $canonical = trim($_POST['canonical_url'] ?? '');
        $robots = trim($_POST['robots'] ?? '');

        if (!$seoTitle && !$seoDesc && !$canonical && !$robots) {
            return;
        }

        $existing = $db->fetch(
            sprintf("SELECT id FROM %s WHERE object_type = ? AND object_id = ?", $db->prefix('seo_meta')),
            [$type, $objectId]
        );

        $data = [
            'object_type' => $type,
            'object_id' => $objectId,
            'seo_title' => $seoTitle ?: null,
            'seo_description' => $seoDesc ?: null,
            'canonical_url' => $canonical ?: null,
            'robots' => $robots ?: null,
        ];

        if ($existing) {
            $db->update('seo_meta', $data, 'id = ?', [$existing['id']]);
        } else {
            $db->insert('seo_meta', $data);
        }
    }
}
