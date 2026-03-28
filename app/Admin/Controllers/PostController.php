<?php
namespace GlassPress\App\Admin\Controllers;

class PostController extends AdminController
{
    public function index(): void
    {
        $db = $this->app->getService('db');
        $status = $_GET['status'] ?? '';
        $search = trim($_GET['s'] ?? '');
        $page = max(1, (int) ($_GET['paged'] ?? 1));
        $perPage = 20;

        $where = "p.post_type = 'post'";
        $params = [];

        if ($status && in_array($status, ['publish', 'draft', 'scheduled', 'pending', 'trash', 'private'])) {
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
             WHERE %s ORDER BY p.created_at DESC LIMIT %d OFFSET %d",
            $db->prefix('posts'), $db->prefix('users'), $where, $perPage, ($page - 1) * $perPage
        ), $params);

        // Status counts
        $counts = [];
        $countRows = $db->fetchAll(sprintf(
            "SELECT status, COUNT(*) as cnt FROM %s WHERE post_type = 'post' GROUP BY status",
            $db->prefix('posts')
        ));
        foreach ($countRows as $row) {
            $counts[$row['status']] = (int) $row['cnt'];
        }

        $this->render('posts.index', [
            'pageTitle' => 'Posts',
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

        $categories = $db->fetchAll(sprintf(
            "SELECT * FROM %s WHERE taxonomy = 'category' ORDER BY name",
            $db->prefix('taxonomies')
        ));

        $tags = $db->fetchAll(sprintf(
            "SELECT * FROM %s WHERE taxonomy = 'tag' ORDER BY name",
            $db->prefix('taxonomies')
        ));

        $authors = $db->fetchAll(sprintf(
            "SELECT id, display_name, username FROM %s WHERE status = 'active' ORDER BY display_name",
            $db->prefix('users')
        ));

        $this->render('posts.editor', [
            'pageTitle' => 'Add New Post',
            'post' => null,
            'categories' => $categories,
            'tags' => $tags,
            'authors' => $authors,
            'postCategories' => [],
            'postTags' => [],
            'seoMeta' => null,
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
        $postType = $_POST['post_type'] ?? 'post';
        $commentStatus = $_POST['comment_status'] ?? 'open';
        $featuredImageId = (int) ($_POST['featured_image_id'] ?? 0) ?: null;
        $isSticky = isset($_POST['is_sticky']) ? 1 : 0;
        $authorId = (int) ($_POST['author_id'] ?? $auth->userId());
        $publishedAt = null;

        if ($status === 'publish') {
            $publishedAt = !empty($_POST['published_at']) ? $_POST['published_at'] : date('Y-m-d H:i:s');
        } elseif ($status === 'scheduled') {
            $publishedAt = !empty($_POST['published_at']) ? $_POST['published_at'] : '';
            if (empty($publishedAt) || strtotime($publishedAt) <= time()) {
                $status = 'publish';
                $publishedAt = date('Y-m-d H:i:s');
            }
        }

        // Only admins/editors can publish
        if (in_array($status, ['publish', 'scheduled']) && !$auth->hasCapability('publish_posts')) {
            $status = 'pending';
        }

        $db->beginTransaction();
        try {
            $postId = $db->insert('posts', [
            'author_id' => $authorId,
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'excerpt' => $excerpt,
            'status' => $status,
            'post_type' => $postType,
            'comment_status' => $commentStatus,
            'featured_image_id' => $featuredImageId,
            'is_sticky' => $isSticky,
            'published_at' => $publishedAt,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Handle categories
        $this->syncTaxonomies($db, $postId, $_POST['categories'] ?? [], 'category');

        // Handle tags
        $tagInput = trim($_POST['tag_input'] ?? '');
        if ($tagInput) {
            $tagNames = array_filter(array_map('trim', explode(',', $tagInput)));
            $tagIds = $this->resolveTagIds($db, $tagNames);
            $this->syncTaxonomies($db, $postId, $tagIds, 'tag');
        }

        // Save SEO meta
        $this->saveSeoMeta($db, 'post', $postId);

        // Create initial revision
        $this->createRevision($db, $postId, $title, $content, $excerpt, $auth->userId());

            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
            $this->redirect($this->app->getAdminUrl('posts'), 'Failed to create post: ' . $e->getMessage(), 'error');
            return;
        }

        $this->redirect($this->app->getAdminUrl("posts/edit/{$postId}"), 'Post created successfully.');
    }

    public function edit(string $id): void
    {
        $db = $this->app->getService('db');
        $auth = $this->app->getService('auth');

        $post = $db->fetch(
            sprintf('SELECT * FROM %s WHERE id = ?', $db->prefix('posts')),
            [(int) $id]
        );

        if (!$post) {
            http_response_code(404);
            echo '<h1>Post not found</h1>';
            return;
        }

        // Check edit permission
        if ($post['author_id'] != $auth->userId() && !$auth->hasCapability('edit_others_posts')) {
            $this->requireCapability('edit_others_posts');
        }

        $categories = $db->fetchAll(sprintf(
            "SELECT * FROM %s WHERE taxonomy = 'category' ORDER BY name",
            $db->prefix('taxonomies')
        ));

        $tags = $db->fetchAll(sprintf(
            "SELECT * FROM %s WHERE taxonomy = 'tag' ORDER BY name",
            $db->prefix('taxonomies')
        ));

        $authors = $db->fetchAll(sprintf(
            "SELECT id, display_name, username FROM %s WHERE status = 'active' ORDER BY display_name",
            $db->prefix('users')
        ));

        // Get post's assigned categories
        $postCategories = $db->fetchAll(sprintf(
            "SELECT t.id FROM %s pt 
             JOIN %s t ON pt.taxonomy_id = t.id 
             WHERE pt.post_id = ? AND t.taxonomy = 'category'",
            $db->prefix('post_taxonomy'), $db->prefix('taxonomies')
        ), [(int) $id]);
        $postCategories = array_column($postCategories, 'id');

        // Get post's assigned tags
        $postTags = $db->fetchAll(sprintf(
            "SELECT t.* FROM %s pt 
             JOIN %s t ON pt.taxonomy_id = t.id 
             WHERE pt.post_id = ? AND t.taxonomy = 'tag'",
            $db->prefix('post_taxonomy'), $db->prefix('taxonomies')
        ), [(int) $id]);

        // Get SEO meta
        $seoMeta = $db->fetch(
            sprintf("SELECT * FROM %s WHERE object_type = 'post' AND object_id = ?", $db->prefix('seo_meta')),
            [(int) $id]
        );

        $this->render('posts.editor', [
            'pageTitle' => 'Edit Post',
            'post' => $post,
            'categories' => $categories,
            'tags' => $tags,
            'authors' => $authors,
            'postCategories' => $postCategories,
            'postTags' => $postTags,
            'seoMeta' => $seoMeta,
            'isNew' => false,
        ]);
    }

    public function update(string $id): void
    {
        $db = $this->app->getService('db');
        $auth = $this->app->getService('auth');
        $postId = (int) $id;

        $post = $db->fetch(
            sprintf('SELECT * FROM %s WHERE id = ?', $db->prefix('posts')),
            [$postId]
        );

        if (!$post) {
            $this->redirect($this->app->getAdminUrl('posts'), 'Post not found.', 'error');
            return;
        }

        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '') ?: $title;
        $slug = $this->generateSlug($slug, 'posts', $postId);
        $content = $this->sanitizeHtml($_POST['content'] ?? '');
        $excerpt = $this->sanitize($_POST['excerpt'] ?? '');
        $status = $_POST['status'] ?? $post['status'];
        $commentStatus = $_POST['comment_status'] ?? 'open';
        $featuredImageId = (int) ($_POST['featured_image_id'] ?? 0) ?: null;
        $isSticky = isset($_POST['is_sticky']) ? 1 : 0;
        $authorId = (int) ($_POST['author_id'] ?? $post['author_id']);
        $publishedAt = $post['published_at'];

        if ($status === 'publish' && !$publishedAt) {
            $publishedAt = !empty($_POST['published_at']) ? $_POST['published_at'] : date('Y-m-d H:i:s');
        } elseif ($status === 'scheduled') {
            $publishedAt = !empty($_POST['published_at']) ? $_POST['published_at'] : '';
        }

        // Check if slug changed - create redirect
        $oldSlug = $post['slug'];
        if ($slug !== $oldSlug && $post['status'] === 'publish') {
            $this->createSlugRedirect($db, $post);
        }

        $db->beginTransaction();
        try {
            $db->update('posts', [
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'excerpt' => $excerpt,
            'status' => $status,
            'comment_status' => $commentStatus,
            'featured_image_id' => $featuredImageId,
            'is_sticky' => $isSticky,
            'author_id' => $authorId,
            'published_at' => $publishedAt,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$postId]);

        // Sync taxonomies
        $this->syncTaxonomies($db, $postId, $_POST['categories'] ?? [], 'category');

        $tagInput = trim($_POST['tag_input'] ?? '');
        $tagIds = [];
        if ($tagInput) {
            $tagNames = array_filter(array_map('trim', explode(',', $tagInput)));
            $tagIds = $this->resolveTagIds($db, $tagNames);
        }
        $this->syncTaxonomies($db, $postId, $tagIds, 'tag');

        // Save SEO meta
        $this->saveSeoMeta($db, 'post', $postId);

        // Create revision
        $this->createRevision($db, $postId, $title, $content, $excerpt, $auth->userId());

            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
            $this->redirect($this->app->getAdminUrl("posts/edit/{$postId}"), 'Failed to update post: ' . $e->getMessage(), 'error');
            return;
        }

        $this->redirect($this->app->getAdminUrl("posts/edit/{$postId}"), 'Post updated successfully.');
    }

    public function delete(string $id): void
    {
        $db = $this->app->getService('db');
        $postId = (int) $id;

        $post = $db->fetch(
            sprintf('SELECT * FROM %s WHERE id = ?', $db->prefix('posts')),
            [$postId]
        );

        if (!$post) {
            $this->redirect($this->app->getAdminUrl('posts'), 'Post not found.', 'error');
            return;
        }

        if ($post['status'] === 'trash') {
            // Permanent delete
            $db->beginTransaction();
            try {
                $db->delete('post_taxonomy', 'post_id = ?', [$postId]);
                $db->delete('post_revisions', 'post_id = ?', [$postId]);
                $db->delete('comments', 'post_id = ?', [$postId]);
                $db->delete('seo_meta', "object_type = 'post' AND object_id = ?", [$postId]);
                $db->delete('posts', 'id = ?', [$postId]);
                $db->commit();
            } catch (\Exception $e) {
                $db->rollBack();
                $this->redirect($this->app->getAdminUrl('posts?status=trash'), 'Failed to delete post.', 'error');
                return;
            }
            $this->redirect($this->app->getAdminUrl('posts?status=trash'), 'Post permanently deleted.');
        } else {
            // Move to trash
            $db->update('posts', ['status' => 'trash', 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$postId]);
            $this->redirect($this->app->getAdminUrl('posts'), 'Post moved to trash.');
        }
    }

    public function bulk(): void
    {
        $action = $_POST['bulk_action'] ?? '';
        $ids = array_map('intval', $_POST['post_ids'] ?? []);
        $db = $this->app->getService('db');

        if (empty($ids)) {
            $this->redirect($this->app->getAdminUrl('posts'), 'No posts selected.', 'warning');
            return;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        switch ($action) {
            case 'trash':
                $db->query(
                    sprintf("UPDATE %s SET status = 'trash', updated_at = NOW() WHERE id IN (%s)", $db->prefix('posts'), $placeholders),
                    $ids
                );
                $this->redirect($this->app->getAdminUrl('posts'), count($ids) . ' posts moved to trash.');
                break;
            case 'publish':
                $db->query(
                    sprintf("UPDATE %s SET status = 'publish', published_at = COALESCE(published_at, NOW()), updated_at = NOW() WHERE id IN (%s)", $db->prefix('posts'), $placeholders),
                    $ids
                );
                $this->redirect($this->app->getAdminUrl('posts'), count($ids) . ' posts published.');
                break;
            case 'draft':
                $db->query(
                    sprintf("UPDATE %s SET status = 'draft', updated_at = NOW() WHERE id IN (%s)", $db->prefix('posts'), $placeholders),
                    $ids
                );
                $this->redirect($this->app->getAdminUrl('posts'), count($ids) . ' posts set to draft.');
                break;
            default:
                $this->redirect($this->app->getAdminUrl('posts'), 'Invalid action.', 'error');
        }
    }

    public function duplicate(string $id): void
    {
        $db = $this->app->getService('db');
        $auth = $this->app->getService('auth');
        $postId = (int) $id;

        $post = $db->fetch(sprintf('SELECT * FROM %s WHERE id = ?', $db->prefix('posts')), [$postId]);
        if (!$post) {
            $this->redirect($this->app->getAdminUrl('posts'), 'Post not found.', 'error');
            return;
        }

        $newSlug = $this->generateSlug($post['slug'] . '-copy', 'posts');

        $newId = $db->insert('posts', [
            'author_id' => $auth->userId(),
            'title' => $post['title'] . ' (Copy)',
            'slug' => $newSlug,
            'content' => $post['content'],
            'excerpt' => $post['excerpt'],
            'status' => 'draft',
            'post_type' => $post['post_type'],
            'comment_status' => $post['comment_status'],
            'featured_image_id' => $post['featured_image_id'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Copy taxonomies
        $taxRelations = $db->fetchAll(
            sprintf('SELECT * FROM %s WHERE post_id = ?', $db->prefix('post_taxonomy')),
            [$postId]
        );
        foreach ($taxRelations as $rel) {
            $db->insert('post_taxonomy', ['post_id' => $newId, 'taxonomy_id' => $rel['taxonomy_id']]);
        }

        $this->redirect($this->app->getAdminUrl("posts/edit/{$newId}"), 'Post duplicated as draft.');
    }

    public function revisions(string $id): void
    {
        $db = $this->app->getService('db');
        $postId = (int) $id;

        $post = $db->fetch(sprintf('SELECT * FROM %s WHERE id = ?', $db->prefix('posts')), [$postId]);
        if (!$post) {
            $this->redirect($this->app->getAdminUrl('posts'), 'Post not found.', 'error');
            return;
        }

        $revisions = $db->fetchAll(sprintf(
            "SELECT r.*, u.display_name as author_name FROM %s r 
             LEFT JOIN %s u ON r.author_id = u.id 
             WHERE r.post_id = ? ORDER BY r.created_at DESC",
            $db->prefix('post_revisions'), $db->prefix('users')
        ), [$postId]);

        $this->render('posts.revisions', [
            'pageTitle' => 'Revisions: ' . $post['title'],
            'post' => $post,
            'revisions' => $revisions,
        ]);
    }

    public function restoreRevision(string $id): void
    {
        $db = $this->app->getService('db');
        $revisionId = (int) $id;

        $revision = $db->fetch(sprintf('SELECT * FROM %s WHERE id = ?', $db->prefix('post_revisions')), [$revisionId]);
        if (!$revision) {
            $this->redirect($this->app->getAdminUrl('posts'), 'Revision not found.', 'error');
            return;
        }

        $db->update('posts', [
            'title' => $revision['title'],
            'content' => $revision['content'],
            'excerpt' => $revision['excerpt'],
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$revision['post_id']]);

        $this->redirect($this->app->getAdminUrl("posts/edit/{$revision['post_id']}"), 'Revision restored.');
    }

    public function autosave(): void
    {
        $db = $this->app->getService('db');
        $auth = $this->app->getService('auth');
        $postId = (int) ($_POST['post_id'] ?? 0);

        if ($postId > 0) {
            $db->update('posts', [
                'title' => trim($_POST['title'] ?? ''),
                'content' => $this->sanitizeHtml($_POST['content'] ?? ''),
                'excerpt' => $this->sanitize($_POST['excerpt'] ?? ''),
                'updated_at' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$postId]);

            $this->createRevision($db, $postId, $_POST['title'] ?? '', $_POST['content'] ?? '', $_POST['excerpt'] ?? '', $auth->userId(), 'autosave');
        }

        $this->json(['success' => true, 'time' => date('H:i:s')]);
    }

    public function apiAutosave(): void
    {
        $this->autosave();
    }

    public function generateSlugApi(): void
    {
        $title = $_GET['title'] ?? '';
        $excludeId = (int) ($_GET['exclude'] ?? 0) ?: null;
        $slug = $this->generateSlug($title, 'posts', $excludeId);
        $this->json(['slug' => $slug]);
    }

    // =====================================
    // Private helpers
    // =====================================

    private function syncTaxonomies($db, int $postId, array $ids, string $type): void
    {
        // Remove existing relationships for this type
        $existingIds = $db->fetchAll(sprintf(
            "SELECT pt.taxonomy_id FROM %s pt 
             JOIN %s t ON pt.taxonomy_id = t.id 
             WHERE pt.post_id = ? AND t.taxonomy = ?",
            $db->prefix('post_taxonomy'), $db->prefix('taxonomies')
        ), [$postId, $type]);

        foreach ($existingIds as $existing) {
            $db->delete('post_taxonomy', 'post_id = ? AND taxonomy_id = ?', [$postId, $existing['taxonomy_id']]);
            // Decrement count
            $db->query(
                sprintf("UPDATE %s SET count = GREATEST(count - 1, 0) WHERE id = ?", $db->prefix('taxonomies')),
                [$existing['taxonomy_id']]
            );
        }

        // Add new relationships
        foreach ($ids as $taxId) {
            $taxId = (int) $taxId;
            if ($taxId > 0) {
                $db->insert('post_taxonomy', ['post_id' => $postId, 'taxonomy_id' => $taxId]);
                $db->query(
                    sprintf("UPDATE %s SET count = count + 1 WHERE id = ?", $db->prefix('taxonomies')),
                    [$taxId]
                );
            }
        }
    }

    private function resolveTagIds($db, array $tagNames): array
    {
        $ids = [];
        foreach ($tagNames as $name) {
            $name = trim($name);
            if (empty($name)) continue;

            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
            $tag = $db->fetch(
                sprintf("SELECT id FROM %s WHERE slug = ? AND taxonomy = 'tag'", $db->prefix('taxonomies')),
                [$slug]
            );

            if ($tag) {
                $ids[] = $tag['id'];
            } else {
                $ids[] = $db->insert('taxonomies', [
                    'name' => $name,
                    'slug' => $slug,
                    'taxonomy' => 'tag',
                    'description' => '',
                    'count' => 0,
                ]);
            }
        }
        return $ids;
    }

    private function saveSeoMeta($db, string $type, int $objectId): void
    {
        $seoTitle = trim($_POST['seo_title'] ?? '');
        $seoDesc = trim($_POST['seo_description'] ?? '');
        $canonical = trim($_POST['canonical_url'] ?? '');
        $robots = trim($_POST['robots'] ?? '');
        $ogTitle = trim($_POST['og_title'] ?? '');
        $ogDesc = trim($_POST['og_description'] ?? '');
        $ogImage = trim($_POST['og_image'] ?? '');
        $focusKeyword = trim($_POST['focus_keyword'] ?? '');

        $hasData = $seoTitle || $seoDesc || $canonical || $robots || $ogTitle || $ogDesc || $ogImage || $focusKeyword;

        if (!$hasData) {
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
            'og_title' => $ogTitle ?: null,
            'og_description' => $ogDesc ?: null,
            'og_image' => $ogImage ?: null,
            'focus_keyword' => $focusKeyword ?: null,
        ];

        if ($existing) {
            $db->update('seo_meta', $data, 'id = ?', [$existing['id']]);
        } else {
            $db->insert('seo_meta', $data);
        }
    }

    private function createRevision($db, int $postId, string $title, string $content, string $excerpt, int $authorId, string $type = 'manual'): void
    {
        // Limit revisions to 25
        $count = $db->count('post_revisions', 'post_id = ?', [$postId]);
        if ($count >= 25) {
            $oldest = $db->fetch(
                sprintf('SELECT id FROM %s WHERE post_id = ? ORDER BY created_at ASC LIMIT 1', $db->prefix('post_revisions')),
                [$postId]
            );
            if ($oldest) {
                $db->delete('post_revisions', 'id = ?', [$oldest['id']]);
            }
        }

        $db->insert('post_revisions', [
            'post_id' => $postId,
            'author_id' => $authorId,
            'title' => $title,
            'content' => $content,
            'excerpt' => $excerpt,
            'revision_type' => $type,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function createSlugRedirect($db, array $post): void
    {
        $settings = $this->app->getService('settings');
        $structure = $settings->get('permalink_structure', '/{slug}');
        $oldUrl = str_replace('{slug}', $post['slug'], $structure);
        $newSlug = trim($_POST['slug'] ?? '') ?: $post['slug'];
        $newUrl = str_replace('{slug}', $newSlug, $structure);

        // Check if redirect already exists
        if ($oldUrl !== $newUrl && !$db->exists('redirects', 'source_url = ?', [$oldUrl])) {
            $db->insert('redirects', [
                'source_url' => $oldUrl,
                'target_url' => $newUrl,
                'status_code' => 301,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
