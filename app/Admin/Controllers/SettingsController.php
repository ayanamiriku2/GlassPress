<?php
namespace GlassPress\App\Admin\Controllers;

class SettingsController extends AdminController
{
    public function general(): void
    {
        $this->requireCapability('manage_options');
        $this->renderSettingsPage('general', 'General Settings');
    }

    public function writing(): void
    {
        $this->requireCapability('manage_options');
        $this->renderSettingsPage('writing', 'Writing Settings');
    }

    public function reading(): void
    {
        $this->requireCapability('manage_options');
        $this->renderSettingsPage('reading', 'Reading Settings');
    }

    public function discussion(): void
    {
        $this->requireCapability('manage_options');
        $this->renderSettingsPage('discussion', 'Discussion Settings');
    }

    public function media(): void
    {
        $this->requireCapability('manage_options');
        $this->renderSettingsPage('media', 'Media Settings');
    }

    public function permalinks(): void
    {
        $this->requireCapability('manage_options');
        $this->renderSettingsPage('permalinks', 'Permalink Settings');
    }

    public function seo(): void
    {
        $this->requireCapability('manage_options');
        $this->renderSettingsPage('seo', 'SEO Settings');
    }

    public function redirects(): void
    {
        $this->requireCapability('manage_options');
        $db = $this->app->getService('db');

        $redirects = $db->fetchAll(sprintf(
            "SELECT * FROM %s ORDER BY created_at DESC",
            $db->prefix('redirects')
        ));

        $this->render('settings.redirects', [
            'pageTitle' => 'Redirects',
            'redirects' => $redirects,
        ]);
    }

    public function appearance(): void
    {
        $this->requireCapability('manage_options');
        $this->renderSettingsPage('appearance', 'Appearance Settings');
    }

    public function advanced(): void
    {
        $this->requireCapability('manage_options');
        $this->renderSettingsPage('advanced', 'Advanced Settings');
    }

    public function save(): void
    {
        $this->requireCapability('manage_options');
        $settings = $this->app->getService('settings');
        $page = $_POST['settings_group'] ?? $_POST['_settings_page'] ?? 'general';

        // Get all POST fields except system fields
        $ignore = ['csrf_token', '_csrf_token', '_settings_page', 'settings_group'];
        foreach ($_POST as $key => $value) {
            if (in_array($key, $ignore)) continue;

            if (is_array($value)) {
                $value = implode(',', $value);
            } else {
                $value = trim($value);
            }

            $settings->set($key, $value);
        }

        // Handle checkbox fields defaults (unchecked = not in POST)
        $checkboxDefaults = $this->getCheckboxDefaults($page);
        foreach ($checkboxDefaults as $key) {
            if (!isset($_POST[$key])) {
                $settings->set($key, '0');
            }
        }

        $this->redirect($this->app->getAdminUrl("settings/{$page}"), 'Settings saved.');
    }

    // Redirects management
    public function storeRedirect(): void
    {
        $this->requireCapability('manage_options');
        $db = $this->app->getService('db');

        $source = trim($_POST['source_url'] ?? '');
        $target = trim($_POST['target_url'] ?? '');
        $code = (int) ($_POST['status_code'] ?? 301);

        if (empty($source) || empty($target)) {
            $this->redirect($this->app->getAdminUrl('settings/redirects'), 'Source and target URLs required.', 'error');
            return;
        }

        $db->insert('redirects', [
            'source_url' => $source,
            'target_url' => $target,
            'status_code' => in_array($code, [301, 302, 307]) ? $code : 301,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->redirect($this->app->getAdminUrl('settings/redirects'), 'Redirect created.');
    }

    public function deleteRedirect(string $id): void
    {
        $this->requireCapability('manage_options');
        $db = $this->app->getService('db');
        $db->delete('redirects', 'id = ?', [(int) $id]);
        $this->redirect($this->app->getAdminUrl('settings/redirects'), 'Redirect deleted.');
    }

    private function renderSettingsPage(string $page, string $title): void
    {
        $settings = $this->app->getService('settings');

        $this->render('settings.' . $page, [
            'pageTitle' => $title,
            'settings' => $settings,
            'settingsPage' => $page,
        ]);
    }

    private function getCheckboxDefaults(string $page): array
    {
        $map = [
            'general' => [],
            'writing' => [],
            'reading' => [],
            'discussion' => ['comments_enabled', 'comment_moderation', 'comment_registration'],
            'media' => [],
            'permalinks' => [],
            'seo' => ['enable_schema', 'enable_opengraph', 'enable_twitter_cards'],
            'appearance' => ['show_sidebar', 'show_author_bio', 'show_post_date', 'show_reading_time', 'show_related_posts'],
            'advanced' => ['enable_cache', 'enable_minify', 'enable_lazy_images', 'force_ssl', 'disable_xmlrpc', 'enable_revisions', 'enable_trash', 'maintenance_mode'],
        ];

        return $map[$page] ?? [];
    }
}
