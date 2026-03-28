<?php
$tabs = ['general' => 'General', 'writing' => 'Writing', 'reading' => 'Reading', 'discussion' => 'Discussion', 'media' => 'Media', 'permalinks' => 'Permalinks', 'seo' => 'SEO', 'redirects' => 'Redirects', 'appearance' => 'Appearance', 'advanced' => 'Advanced'];
?>
<div class="content-header"><div class="content-header-left"><h1><?= $pageTitle ?></h1></div></div>
<div class="settings-tabs">
    <?php foreach ($tabs as $key => $label): ?>
    <a href="<?= $adminUrl ?>/settings/<?= $key ?>" class="settings-tab <?= $settingsPage === $key ? 'active' : '' ?>"><?= $label ?></a>
    <?php endforeach; ?>
</div>

<div class="glass-card settings-form">
    <form method="post" action="<?= $adminUrl ?>/settings/save">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
        <input type="hidden" name="_settings_page" value="writing">

        <div class="form-group">
            <label>Default Post Category</label>
            <select name="default_category" class="form-input">
                <?php
                $db = $app->getService('db');
                $cats = $db->fetchAll(sprintf("SELECT * FROM %s WHERE taxonomy = 'category' ORDER BY name", $db->prefix('taxonomies')));
                $defaultCat = $settings->get('default_category', '1');
                foreach ($cats as $cat):
                ?>
                <option value="<?= $cat['id'] ?>" <?= $defaultCat == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Default Post Status</label>
            <select name="default_post_status" class="form-input">
                <?php $dps = $settings->get('default_post_status', 'draft'); ?>
                <option value="draft" <?= $dps === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="published" <?= $dps === 'published' ? 'selected' : '' ?>>Published</option>
            </select>
        </div>

        <div class="form-group">
            <label>Editor Type</label>
            <select name="editor_type" class="form-input">
                <?php $et = $settings->get('editor_type', 'visual'); ?>
                <option value="visual" <?= $et === 'visual' ? 'selected' : '' ?>>Visual Editor</option>
                <option value="html" <?= $et === 'html' ? 'selected' : '' ?>>HTML Editor</option>
            </select>
        </div>

        <div class="form-group">
            <label>Auto-save Interval (seconds)</label>
            <input type="number" name="autosave_interval" class="form-input" value="<?= (int) $settings->get('autosave_interval', 30) ?>" min="10" max="300">
        </div>

        <div class="publish-actions">
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </div>
    </form>
</div>

<style>
.settings-tabs { display: flex; gap: 4px; margin-bottom: 24px; flex-wrap: wrap; }
.settings-tab { padding: 8px 16px; border-radius: 8px 8px 0 0; color: var(--text-muted); text-decoration: none; font-size: 14px; transition: all .2s; border: 1px solid transparent; border-bottom: none; }
.settings-tab:hover { color: var(--text); background: var(--glass-bg); }
.settings-tab.active { color: var(--text); background: var(--glass-bg); border-color: var(--glass-border); font-weight: 600; }
.settings-form { max-width: 700px; }
.publish-actions { display: flex; gap: 8px; justify-content: flex-end; padding-top: 16px; border-top: 1px solid var(--glass-border); margin-top: 8px; }
</style>
