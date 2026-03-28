<?php $tabs = ['general'=>'General','writing'=>'Writing','reading'=>'Reading','discussion'=>'Discussion','media'=>'Media','permalinks'=>'Permalinks','seo'=>'SEO','redirects'=>'Redirects','appearance'=>'Appearance','advanced'=>'Advanced']; ?>
<div class="content-header"><div class="content-header-left"><h1><?= $pageTitle ?></h1></div></div>
<div class="settings-tabs"><?php foreach ($tabs as $key => $label): ?><a href="<?= $adminUrl ?>/settings/<?= $key ?>" class="settings-tab <?= $settingsPage === $key ? 'active' : '' ?>"><?= $label ?></a><?php endforeach; ?></div>

<div class="glass-card settings-form">
    <form method="post" action="<?= $adminUrl ?>/settings/save">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
        <input type="hidden" name="_settings_page" value="media">

        <h3>Thumbnail Size</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
            <div class="form-group">
                <label>Width (px)</label>
                <input type="number" name="thumb_width" class="form-input" value="<?= (int) $settings->get('thumb_width', 150) ?>" min="50">
            </div>
            <div class="form-group">
                <label>Height (px)</label>
                <input type="number" name="thumb_height" class="form-input" value="<?= (int) $settings->get('thumb_height', 150) ?>" min="50">
            </div>
        </div>

        <h3>Medium Size</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
            <div class="form-group">
                <label>Max Width (px)</label>
                <input type="number" name="medium_width" class="form-input" value="<?= (int) $settings->get('medium_width', 300) ?>" min="100">
            </div>
            <div class="form-group">
                <label>Max Height (px)</label>
                <input type="number" name="medium_height" class="form-input" value="<?= (int) $settings->get('medium_height', 300) ?>" min="100">
            </div>
        </div>

        <h3>Large Size</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
            <div class="form-group">
                <label>Max Width (px)</label>
                <input type="number" name="large_width" class="form-input" value="<?= (int) $settings->get('large_width', 1024) ?>" min="200">
            </div>
            <div class="form-group">
                <label>Max Height (px)</label>
                <input type="number" name="large_height" class="form-input" value="<?= (int) $settings->get('large_height', 1024) ?>" min="200">
            </div>
        </div>

        <div class="form-group">
            <label>Upload Directory Organization</label>
            <select name="upload_organize" class="form-input">
                <?php $org = $settings->get('upload_organize', 'year-month'); ?>
                <option value="year-month" <?= $org === 'year-month' ? 'selected' : '' ?>>Year/Month folders</option>
                <option value="year" <?= $org === 'year' ? 'selected' : '' ?>>Year folders only</option>
                <option value="flat" <?= $org === 'flat' ? 'selected' : '' ?>>No folders</option>
            </select>
        </div>

        <div class="publish-actions">
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </div>
    </form>
</div>

<style>
.settings-tabs{display:flex;gap:4px;margin-bottom:24px;flex-wrap:wrap}.settings-tab{padding:8px 16px;border-radius:8px 8px 0 0;color:var(--text-muted);text-decoration:none;font-size:14px;transition:all .2s;border:1px solid transparent;border-bottom:none}.settings-tab:hover{color:var(--text);background:var(--glass-bg)}.settings-tab.active{color:var(--text);background:var(--glass-bg);border-color:var(--glass-border);font-weight:600}.settings-form{max-width:700px}.publish-actions{display:flex;gap:8px;justify-content:flex-end;padding-top:16px;border-top:1px solid var(--glass-border);margin-top:8px}
</style>
