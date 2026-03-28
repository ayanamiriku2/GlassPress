<?php
namespace GlassPress\App\Admin\Controllers;

class TaxonomyController extends AdminController
{
    // =====================
    // CATEGORIES
    // =====================

    public function categories(): void
    {
        $db = $this->app->getService('db');
        $search = trim($_GET['s'] ?? '');

        $where = "taxonomy = 'category'";
        $params = [];

        if ($search) {
            $where .= ' AND (name LIKE ? OR description LIKE ?)';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }

        $categories = $db->fetchAll(sprintf(
            "SELECT * FROM %s WHERE %s ORDER BY name ASC",
            $db->prefix('taxonomies'), $where
        ), $params);

        $this->render('taxonomies.categories', [
            'pageTitle' => 'Categories',
            'items' => $categories,
            'search' => $search,
        ]);
    }

    public function storeCategory(): void
    {
        $this->requireCapability('manage_categories');
        $db = $this->app->getService('db');

        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '') ?: $name;
        $slug = $this->generateSlug($slug, 'taxonomies');
        $description = $this->sanitize($_POST['description'] ?? '');
        $parentId = (int) ($_POST['parent_id'] ?? 0) ?: null;

        if (empty($name)) {
            $this->redirect($this->app->getAdminUrl('categories'), 'Name is required.', 'error');
            return;
        }

        $db->insert('taxonomies', [
            'name' => $name,
            'slug' => $slug,
            'taxonomy' => 'category',
            'description' => $description,
            'parent_id' => $parentId,
            'count' => 0,
        ]);

        $this->redirect($this->app->getAdminUrl('categories'), 'Category created.');
    }

    public function editCategory(string $id): void
    {
        $db = $this->app->getService('db');
        $item = $db->fetch(
            sprintf("SELECT * FROM %s WHERE id = ? AND taxonomy = 'category'", $db->prefix('taxonomies')),
            [(int) $id]
        );

        if (!$item) {
            $this->redirect($this->app->getAdminUrl('categories'), 'Category not found.', 'error');
            return;
        }

        $allCategories = $db->fetchAll(sprintf(
            "SELECT * FROM %s WHERE taxonomy = 'category' AND id != ? ORDER BY name",
            $db->prefix('taxonomies')
        ), [(int) $id]);

        $this->render('taxonomies.edit_category', [
            'pageTitle' => 'Edit Category',
            'item' => $item,
            'allCategories' => $allCategories,
        ]);
    }

    public function updateCategory(string $id): void
    {
        $this->requireCapability('manage_categories');
        $db = $this->app->getService('db');
        $catId = (int) $id;

        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '') ?: $name;
        $slug = $this->generateSlug($slug, 'taxonomies', $catId);
        $description = $this->sanitize($_POST['description'] ?? '');
        $parentId = (int) ($_POST['parent_id'] ?? 0) ?: null;

        $db->update('taxonomies', [
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'parent_id' => $parentId,
        ], 'id = ?', [$catId]);

        $this->redirect($this->app->getAdminUrl('categories'), 'Category updated.');
    }

    public function deleteCategory(string $id): void
    {
        $this->requireCapability('manage_categories');
        $db = $this->app->getService('db');
        $catId = (int) $id;

        // Don't delete if it's the only category (must have at least "Uncategorized")
        $count = $db->count('taxonomies', "taxonomy = 'category'");
        if ($count <= 1) {
            $this->redirect($this->app->getAdminUrl('categories'), 'Cannot delete the last category.', 'error');
            return;
        }

        // Remove post relationships
        $db->delete('post_taxonomy', 'taxonomy_id = ?', [$catId]);
        $db->delete('taxonomies', 'id = ?', [$catId]);

        $this->redirect($this->app->getAdminUrl('categories'), 'Category deleted.');
    }

    // =====================
    // TAGS
    // =====================

    public function tags(): void
    {
        $db = $this->app->getService('db');
        $search = trim($_GET['s'] ?? '');

        $where = "taxonomy = 'tag'";
        $params = [];

        if ($search) {
            $where .= ' AND (name LIKE ?)';
            $params[] = '%' . $search . '%';
        }

        $tags = $db->fetchAll(sprintf(
            "SELECT * FROM %s WHERE %s ORDER BY name ASC",
            $db->prefix('taxonomies'), $where
        ), $params);

        $this->render('taxonomies.tags', [
            'pageTitle' => 'Tags',
            'items' => $tags,
            'search' => $search,
        ]);
    }

    public function storeTag(): void
    {
        $this->requireCapability('manage_categories');
        $db = $this->app->getService('db');

        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '') ?: $name;
        $slug = $this->generateSlug($slug, 'taxonomies');
        $description = $this->sanitize($_POST['description'] ?? '');

        if (empty($name)) {
            $this->redirect($this->app->getAdminUrl('tags'), 'Name is required.', 'error');
            return;
        }

        $db->insert('taxonomies', [
            'name' => $name,
            'slug' => $slug,
            'taxonomy' => 'tag',
            'description' => $description,
            'count' => 0,
        ]);

        $this->redirect($this->app->getAdminUrl('tags'), 'Tag created.');
    }

    public function editTag(string $id): void
    {
        $db = $this->app->getService('db');
        $item = $db->fetch(
            sprintf("SELECT * FROM %s WHERE id = ? AND taxonomy = 'tag'", $db->prefix('taxonomies')),
            [(int) $id]
        );

        if (!$item) {
            $this->redirect($this->app->getAdminUrl('tags'), 'Tag not found.', 'error');
            return;
        }

        $this->render('taxonomies.edit_tag', [
            'pageTitle' => 'Edit Tag',
            'item' => $item,
        ]);
    }

    public function updateTag(string $id): void
    {
        $this->requireCapability('manage_categories');
        $db = $this->app->getService('db');
        $tagId = (int) $id;

        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '') ?: $name;
        $slug = $this->generateSlug($slug, 'taxonomies', $tagId);
        $description = $this->sanitize($_POST['description'] ?? '');

        $db->update('taxonomies', [
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
        ], 'id = ?', [$tagId]);

        $this->redirect($this->app->getAdminUrl('tags'), 'Tag updated.');
    }

    public function deleteTag(string $id): void
    {
        $this->requireCapability('manage_categories');
        $db = $this->app->getService('db');
        $tagId = (int) $id;

        $db->delete('post_taxonomy', 'taxonomy_id = ?', [$tagId]);
        $db->delete('taxonomies', 'id = ?', [$tagId]);

        $this->redirect($this->app->getAdminUrl('tags'), 'Tag deleted.');
    }
}
