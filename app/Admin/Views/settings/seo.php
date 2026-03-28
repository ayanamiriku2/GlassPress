<?php $tabs = ['general'=>'General','writing'=>'Writing','reading'=>'Reading','discussion'=>'Discussion','media'=>'Media','permalinks'=>'Permalinks','seo'=>'SEO','redirects'=>'Redirects','appearance'=>'Appearance','advanced'=>'Advanced']; ?>
<div class="content-header"><div class="content-header-left"><h1><?= $pageTitle ?></h1></div></div>
<div class="settings-tabs"><?php foreach ($tabs as $key => $label): ?><a href="<?= $adminUrl ?>/settings/<?= $key ?>" class="settings-tab <?= $settingsPage === $key ? 'active' : '' ?>"><?= $label ?></a><?php endforeach; ?></div>

<div class="glass-card settings-form">
    <form method="post" action="<?= $adminUrl ?>/settings/save">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
        <input type="hidden" name="_settings_page" value="seo">

        <div class="form-group">
            <label>Homepage Meta Title</label>
            <input type="text" name="seo_home_title" class="form-input" maxlength="70" value="<?= htmlspecialchars($settings->get('seo_home_title', '')) ?>" placeholder="Site Title - Tagline">
        </div>

        <div class="form-group">
            <label>Homepage Meta Description</label>
            <textarea name="seo_home_description" rows="2" class="form-input" maxlength="160"><?= htmlspecialchars($settings->get('seo_home_description', '')) ?></textarea>
        </div>

        <div class="form-group">
            <label>Title Separator</label>
            <select name="seo_title_separator" class="form-input">
                <?php $sep = $settings->get('seo_title_separator', '|'); ?>
                <option value="|" <?= $sep === '|' ? 'selected' : '' ?>>| (pipe)</option>
                <option value="-" <?= $sep === '-' ? 'selected' : '' ?>>- (dash)</option>
                <option value="–" <?= $sep === '–' ? 'selected' : '' ?>>– (en dash)</option>
                <option value="•" <?= $sep === '•' ? 'selected' : '' ?>>• (bullet)</option>
            </select>
        </div>

        <h3 style="margin-top:24px">Structured Data</h3>

        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="enable_schema" value="1" <?= $settings->get('enable_schema', '1') === '1' ? 'checked' : '' ?>>
                Enable JSON-LD structured data
            </label>
        </div>

        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="enable_opengraph" value="1" <?= $settings->get('enable_opengraph', '1') === '1' ? 'checked' : '' ?>>
                Enable Open Graph meta tags
            </label>
        </div>

        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="enable_twitter_cards" value="1" <?= $settings->get('enable_twitter_cards', '1') === '1' ? 'checked' : '' ?>>
                Enable Twitter Card meta tags
            </label>
        </div>

        <h3 style="margin-top:24px">Social</h3>

        <div class="form-group">
            <label>Default Social Image URL</label>
            <input type="url" name="seo_default_image" class="form-input" value="<?= htmlspecialchars($settings->get('seo_default_image', '')) ?>">
        </div>

        <div class="form-group">
            <label>Twitter Handle</label>
            <input type="text" name="twitter_handle" class="form-input" value="<?= htmlspecialchars($settings->get('twitter_handle', '')) ?>" placeholder="@username">
        </div>

        <h3 style="margin-top:24px">Verification</h3>

        <div class="form-group">
            <label>Google Site Verification</label>
            <input type="text" name="google_verification" class="form-input" value="<?= htmlspecialchars($settings->get('google_verification', '')) ?>">
        </div>

        <div class="form-group">
            <label>Bing Site Verification</label>
            <input type="text" name="bing_verification" class="form-input" value="<?= htmlspecialchars($settings->get('bing_verification', '')) ?>">
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
