<?php $tabs = ['general'=>'General','writing'=>'Writing','reading'=>'Reading','discussion'=>'Discussion','media'=>'Media','permalinks'=>'Permalinks','seo'=>'SEO','redirects'=>'Redirects','appearance'=>'Appearance','advanced'=>'Advanced']; ?>
<div class="content-header"><div class="content-header-left"><h1><?= $pageTitle ?></h1></div></div>
<div class="settings-tabs"><?php foreach ($tabs as $key => $label): ?><a href="<?= $adminUrl ?>/settings/<?= $key ?>" class="settings-tab <?= $settingsPage === $key ? 'active' : '' ?>"><?= $label ?></a><?php endforeach; ?></div>

<div class="glass-card settings-form">
    <form method="post" action="<?= $adminUrl ?>/settings/save">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
        <input type="hidden" name="_settings_page" value="discussion">

        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="comments_enabled" value="1" <?= $settings->get('comments_enabled', '1') === '1' ? 'checked' : '' ?>>
                Allow comments on new posts by default
            </label>
        </div>

        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="comment_moderation" value="1" <?= $settings->get('comment_moderation', '1') === '1' ? 'checked' : '' ?>>
                Comments must be manually approved
            </label>
        </div>

        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="comment_registration" value="1" <?= $settings->get('comment_registration', '0') === '1' ? 'checked' : '' ?>>
                Users must be registered to comment
            </label>
        </div>

        <div class="form-group">
            <label>Nested Comments Depth</label>
            <select name="comment_nesting_depth" class="form-input">
                <?php $depth = $settings->get('comment_nesting_depth', '3'); ?>
                <?php for ($i = 1; $i <= 10; $i++): ?>
                <option value="<?= $i ?>" <?= $depth == $i ? 'selected' : '' ?>><?= $i ?> level<?= $i > 1 ? 's' : '' ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Comments Per Page</label>
            <input type="number" name="comments_per_page" class="form-input" value="<?= (int) $settings->get('comments_per_page', 50) ?>" min="5" max="200">
        </div>

        <div class="form-group">
            <label>Close Comments After (days, 0 = never)</label>
            <input type="number" name="close_comments_days" class="form-input" value="<?= (int) $settings->get('close_comments_days', 0) ?>" min="0">
        </div>

        <div class="publish-actions">
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </div>
    </form>
</div>

<style>
.settings-tabs{display:flex;gap:4px;margin-bottom:24px;flex-wrap:wrap}.settings-tab{padding:8px 16px;border-radius:8px 8px 0 0;color:var(--text-muted);text-decoration:none;font-size:14px;transition:all .2s;border:1px solid transparent;border-bottom:none}.settings-tab:hover{color:var(--text);background:var(--glass-bg)}.settings-tab.active{color:var(--text);background:var(--glass-bg);border-color:var(--glass-border);font-weight:600}.settings-form{max-width:700px}.publish-actions{display:flex;gap:8px;justify-content:flex-end;padding-top:16px;border-top:1px solid var(--glass-border);margin-top:8px}
.checkbox-label{display:flex;align-items:center;gap:8px;font-size:14px;cursor:pointer}
</style>
