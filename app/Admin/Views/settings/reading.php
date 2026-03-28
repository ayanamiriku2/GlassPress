<?php $tabs = ['general'=>'General','writing'=>'Writing','reading'=>'Reading','discussion'=>'Discussion','media'=>'Media','permalinks'=>'Permalinks','seo'=>'SEO','redirects'=>'Redirects','appearance'=>'Appearance','advanced'=>'Advanced']; ?>
<div class="content-header"><div class="content-header-left"><h1><?= $pageTitle ?></h1></div></div>
<div class="settings-tabs"><?php foreach ($tabs as $key => $label): ?><a href="<?= $adminUrl ?>/settings/<?= $key ?>" class="settings-tab <?= $settingsPage === $key ? 'active' : '' ?>"><?= $label ?></a><?php endforeach; ?></div>

<div class="glass-card settings-form">
    <form method="post" action="<?= $adminUrl ?>/settings/save">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
        <input type="hidden" name="_settings_page" value="reading">

        <div class="form-group">
            <label>Posts Per Page</label>
            <input type="number" name="posts_per_page" class="form-input" value="<?= (int) $settings->get('posts_per_page', 10) ?>" min="1" max="100">
        </div>

        <div class="form-group">
            <label>Homepage Display</label>
            <select name="homepage_display" class="form-input">
                <?php $hd = $settings->get('homepage_display', 'posts'); ?>
                <option value="posts" <?= $hd === 'posts' ? 'selected' : '' ?>>Latest posts</option>
                <option value="page" <?= $hd === 'page' ? 'selected' : '' ?>>Static page</option>
            </select>
        </div>

        <div class="form-group">
            <label>Static Homepage (page ID, if applicable)</label>
            <input type="number" name="homepage_page_id" class="form-input" value="<?= $settings->get('homepage_page_id', '') ?>">
        </div>

        <div class="form-group">
            <label>Feed Display</label>
            <select name="show_full_content" class="form-input">
                <?php $fc = $settings->get('show_full_content', '0'); ?>
                <option value="0" <?= $fc === '0' ? 'selected' : '' ?>>Excerpt</option>
                <option value="1" <?= $fc === '1' ? 'selected' : '' ?>>Full content</option>
            </select>
        </div>

        <div class="form-group">
            <label>Excerpt Length (words)</label>
            <input type="number" name="excerpt_length" class="form-input" value="<?= (int) $settings->get('excerpt_length', 55) ?>" min="10" max="300">
        </div>

        <div class="publish-actions">
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </div>
    </form>
</div>

<style>
.settings-tabs{display:flex;gap:4px;margin-bottom:24px;flex-wrap:wrap}.settings-tab{padding:8px 16px;border-radius:8px 8px 0 0;color:var(--text-muted);text-decoration:none;font-size:14px;transition:all .2s;border:1px solid transparent;border-bottom:none}.settings-tab:hover{color:var(--text);background:var(--glass-bg)}.settings-tab.active{color:var(--text);background:var(--glass-bg);border-color:var(--glass-border);font-weight:600}.settings-form{max-width:700px}.publish-actions{display:flex;gap:8px;justify-content:flex-end;padding-top:16px;border-top:1px solid var(--glass-border);margin-top:8px}
</style>
