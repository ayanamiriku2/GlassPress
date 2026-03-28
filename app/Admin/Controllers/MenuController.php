<?php
namespace GlassPress\App\Admin\Controllers;

class MenuController extends AdminController
{
    public function index(): void
    {
        $this->requireCapability('manage_options');
        $db = $this->app->getService('db');

        $menus = $db->fetchAll(sprintf("SELECT * FROM %s ORDER BY name", $db->prefix('menus')));
        $selectedId = (int) ($_GET['menu'] ?? ($menus[0]['id'] ?? 0));
        $selectedMenu = null;
        $menuItems = [];

        if ($selectedId) {
            $selectedMenu = $db->fetch(
                sprintf('SELECT * FROM %s WHERE id = ?', $db->prefix('menus')),
                [$selectedId]
            );
            $menuItems = $db->fetchAll(sprintf(
                "SELECT * FROM %s WHERE menu_id = ? ORDER BY sort_order ASC",
                $db->prefix('menu_items')
            ), [$selectedId]);
        }

        // Get pages and categories for adding to menus
        $pages = $db->fetchAll(sprintf(
            "SELECT id, title FROM %s WHERE post_type = 'page' AND status = 'publish' ORDER BY title",
            $db->prefix('posts')
        ));

        $categories = $db->fetchAll(sprintf(
            "SELECT id, name FROM %s WHERE taxonomy = 'category' ORDER BY name",
            $db->prefix('taxonomies')
        ));

        $this->render('menus.index', [
            'pageTitle' => 'Menus',
            'menus' => $menus,
            'selectedMenu' => $selectedMenu,
            'menuItems' => $menuItems,
            'pages' => $pages,
            'categories' => $categories,
        ]);
    }

    public function store(): void
    {
        $this->requireCapability('manage_options');
        $db = $this->app->getService('db');

        $name = trim($_POST['name'] ?? '');
        $location = $this->sanitize($_POST['location'] ?? 'primary');

        if (empty($name)) {
            $this->redirect($this->app->getAdminUrl('menus'), 'Menu name is required.', 'error');
            return;
        }

        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));

        $menuId = $db->insert('menus', [
            'name' => $name,
            'slug' => $slug,
            'location' => $location,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->redirect($this->app->getAdminUrl("menus?menu={$menuId}"), 'Menu created.');
    }

    public function update(string $id): void
    {
        $this->requireCapability('manage_options');
        $db = $this->app->getService('db');
        $menuId = (int) $id;

        $name = trim($_POST['name'] ?? '');
        $location = $this->sanitize($_POST['location'] ?? 'primary');

        $db->update('menus', ['name' => $name, 'location' => $location], 'id = ?', [$menuId]);

        // Update menu items
        // First remove all existing items
        $db->delete('menu_items', 'menu_id = ?', [$menuId]);

        // Re-insert from form data
        $titles = $_POST['item_title'] ?? [];
        $urls = $_POST['item_url'] ?? [];
        $types = $_POST['item_type'] ?? [];
        $objectIds = $_POST['item_object_id'] ?? [];
        $parentIndexes = $_POST['item_parent'] ?? [];
        $targets = $_POST['item_target'] ?? [];

        foreach ($titles as $i => $title) {
            $title = trim($title);
            if (empty($title)) continue;

            $db->insert('menu_items', [
                'menu_id' => $menuId,
                'title' => $title,
                'url' => trim($urls[$i] ?? '#'),
                'item_type' => $types[$i] ?? 'custom',
                'object_id' => (int) ($objectIds[$i] ?? 0) ?: null,
                'parent_id' => null,
                'sort_order' => $i,
                'target' => ($targets[$i] ?? '') === '_blank' ? '_blank' : '',
                'css_class' => '',
            ]);
        }

        $this->redirect($this->app->getAdminUrl("menus?menu={$menuId}"), 'Menu updated.');
    }

    public function delete(string $id): void
    {
        $this->requireCapability('manage_options');
        $db = $this->app->getService('db');
        $menuId = (int) $id;

        $db->beginTransaction();
        try {
            $db->delete('menu_items', 'menu_id = ?', [$menuId]);
            $db->delete('menus', 'id = ?', [$menuId]);
            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
            $this->redirect($this->app->getAdminUrl('menus'), 'Failed to delete menu.', 'error');
            return;
        }

        $this->redirect($this->app->getAdminUrl('menus'), 'Menu deleted.');
    }

    public function addItem(): void
    {
        $this->requireCapability('manage_options');
        $db = $this->app->getService('db');
        $menuId = (int) ($_POST['menu_id'] ?? 0);
        $type = $_POST['type'] ?? 'custom';

        if (!$menuId) {
            $this->redirect($this->app->getAdminUrl('menus'), 'No menu selected.', 'error');
            return;
        }

        $maxOrder = (int) $db->fetchColumn(
            sprintf('SELECT MAX(sort_order) FROM %s WHERE menu_id = ?', $db->prefix('menu_items')),
            [$menuId]
        );

        if ($type === 'custom') {
            $db->insert('menu_items', [
                'menu_id' => $menuId,
                'title' => trim($_POST['title'] ?? 'Custom Link'),
                'url' => trim($_POST['url'] ?? '#'),
                'type' => 'custom',
                'sort_order' => $maxOrder + 1,
            ]);
        } elseif ($type === 'page') {
            $pageIds = $_POST['page_ids'] ?? [];
            foreach ($pageIds as $pageId) {
                $page = $db->fetch(sprintf('SELECT id, title, slug FROM %s WHERE id = ?', $db->prefix('posts')), [(int) $pageId]);
                if ($page) {
                    $maxOrder++;
                    $db->insert('menu_items', [
                        'menu_id' => $menuId,
                        'title' => $page['title'],
                        'url' => '/' . $page['slug'],
                        'type' => 'page',
                        'object_id' => $page['id'],
                        'sort_order' => $maxOrder,
                    ]);
                }
            }
        } elseif ($type === 'category') {
            $catIds = $_POST['category_ids'] ?? [];
            foreach ($catIds as $catId) {
                $cat = $db->fetch(sprintf("SELECT id, name, slug FROM %s WHERE id = ?", $db->prefix('taxonomies')), [(int) $catId]);
                if ($cat) {
                    $maxOrder++;
                    $db->insert('menu_items', [
                        'menu_id' => $menuId,
                        'title' => $cat['name'],
                        'url' => '/category/' . $cat['slug'],
                        'type' => 'category',
                        'object_id' => $cat['id'],
                        'sort_order' => $maxOrder,
                    ]);
                }
            }
        }

        $this->redirect($this->app->getAdminUrl("menus?menu={$menuId}"), 'Item(s) added.');
    }
}
