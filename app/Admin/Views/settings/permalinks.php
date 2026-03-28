<?php $tabs = ['general'=>'General','writing'=>'Writing','reading'=>'Reading','discussion'=>'Discussion','media'=>'Media','permalinks'=>'Permalinks','seo'=>'SEO','redirects'=>'Redirects','appearance'=>'Appearance','advanced'=>'Advanced']; ?>
<div class="content-header"><div class="content-header-left"><h1><?= $pageTitle ?></h1></div></div>
<div class="settings-tabs"><?php foreach ($tabs as $key => $label): ?><a href="<?= $adminUrl ?>/settings/<?= $key ?>" class="settings-tab <?= $settingsPage === $key ? 'active' : '' ?>"><?= $label ?></a><?php endforeach; ?></div>

<div class="glass-card settings-form">
    <form method="post" action="<?= $adminUrl ?>/settings/save">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
        <input type="hidden" name="_settings_page" value="permalinks">

        <div class="form-group">
            <label>Permalink Structure</label>
            <?php $struct = $settings->get('permalink_structure', '/{slug}'); ?>
            <div style="display:flex;flex-direction:column;gap:8px">
                <label class="radio-label"><input type="radio" name="permalink_structure" value="/{slug}" <?= $struct === '/{slug}' ? 'checked' : '' ?>> <code>/{slug}</code> <span class="form-hint">Post Name</span></label>
                <label class="radio-label"><input type="radio" name="permalink_structure" value="/{year}/{month}/{slug}" <?= $struct === '/{year}/{month}/{slug}' ? 'checked' : '' ?>> <code>/{year}/{month}/{slug}</code> <span class="form-hint">Date and Name</span></label>
                <label class="radio-label"><input type="radio" name="permalink_structure" value="/blog/{slug}" <?= $struct === '/blog/{slug}' ? 'checked' : '' ?>> <code>/blog/{slug}</code> <span class="form-hint">Blog Prefix</span></label>
                <label class="radio-label"><input type="radio" name="permalink_structure" value="/{category}/{slug}" <?= $struct === '/{category}/{slug}' ? 'checked' : '' ?>> <code>/{category}/{slug}</code> <span class="form-hint">Category and Name</span></label>
            </div>
        </div>

        <div class="form-group">
            <label>Category Base</label>
            <input type="text" name="category_base" class="form-input" value="<?= htmlspecialchars($settings->get('category_base', 'category')) ?>" placeholder="category">
        </div>

        <div class="form-group">
            <label>Tag Base</label>
            <input type="text" name="tag_base" class="form-input" value="<?= htmlspecialchars($settings->get('tag_base', 'tag')) ?>" placeholder="tag">
        </div>

        <div class="publish-actions">
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </div>
    </form>
</div>

<style>
.settings-tabs{display:flex;gap:4px;margin-bottom:24px;flex-wrap:wrap}.settings-tab{padding:8px 16px;border-radius:8px 8px 0 0;color:var(--text-muted);text-decoration:none;font-size:14px;transition:all .2s;border:1px solid transparent;border-bottom:none}.settings-tab:hover{color:var(--text);background:var(--glass-bg)}.settings-tab.active{color:var(--text);background:var(--glass-bg);border-color:var(--glass-border);font-weight:600}.settings-form{max-width:700px}.publish-actions{display:flex;gap:8px;justify-content:flex-end;padding-top:16px;border-top:1px solid var(--glass-border);margin-top:8px}
.radio-label{display:flex;align-items:center;gap:8px;font-size:14px;cursor:pointer}
.radio-label .form-hint{margin:0}
</style>
