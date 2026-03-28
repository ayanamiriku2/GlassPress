<div class="content-header"><div class="content-header-left"><h1>Appearance</h1></div></div>

<?php $tabs = ['general'=>'General','writing'=>'Writing','reading'=>'Reading','discussion'=>'Discussion','media'=>'Media','permalinks'=>'Permalinks','seo'=>'SEO','redirects'=>'Redirects','appearance'=>'Appearance','advanced'=>'Advanced']; $settingsPage = 'appearance'; ?>
<div class="settings-tabs"><?php foreach ($tabs as $key => $label): ?><a href="<?= $adminUrl ?>/settings/<?= $key ?>" class="settings-tab <?= $settingsPage === $key ? 'active' : '' ?>"><?= $label ?></a><?php endforeach; ?></div>

<form method="post" action="<?= $adminUrl ?>/settings/save">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    <input type="hidden" name="settings_group" value="appearance">

    <div class="glass-card" style="max-width:700px">
        <h3 class="card-title">Layout</h3>

        <div class="form-group">
            <label class="form-label">Sidebar Position</label>
            <select name="sidebar_position" class="form-input">
                <option value="right" <?= ($settings['sidebar_position'] ?? 'right') === 'right' ? 'selected' : '' ?>>Right</option>
                <option value="left" <?= ($settings['sidebar_position'] ?? 'right') === 'left' ? 'selected' : '' ?>>Left</option>
                <option value="none" <?= ($settings['sidebar_position'] ?? 'right') === 'none' ? 'selected' : '' ?>>No Sidebar</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">
                <input type="checkbox" name="show_sidebar" value="1" <?= ($settings['show_sidebar'] ?? '1') === '1' ? 'checked' : '' ?>>
                Show sidebar on blog pages
            </label>
        </div>

        <div class="form-group">
            <label class="form-label">
                <input type="checkbox" name="show_author_bio" value="1" <?= ($settings['show_author_bio'] ?? '1') === '1' ? 'checked' : '' ?>>
                Show author bio below posts
            </label>
        </div>

        <div class="form-group">
            <label class="form-label">
                <input type="checkbox" name="show_post_date" value="1" <?= ($settings['show_post_date'] ?? '1') === '1' ? 'checked' : '' ?>>
                Show post date
            </label>
        </div>

        <div class="form-group">
            <label class="form-label">
                <input type="checkbox" name="show_reading_time" value="1" <?= ($settings['show_reading_time'] ?? '0') === '1' ? 'checked' : '' ?>>
                Show estimated reading time
            </label>
        </div>

        <div class="form-group">
            <label class="form-label">
                <input type="checkbox" name="show_related_posts" value="1" <?= ($settings['show_related_posts'] ?? '1') === '1' ? 'checked' : '' ?>>
                Show related posts after content
            </label>
        </div>
    </div>

    <div class="glass-card" style="max-width:700px;margin-top:24px">
        <h3 class="card-title">Branding</h3>

        <div class="form-group">
            <label class="form-label">Logo URL</label>
            <input type="text" name="site_logo" class="form-input" value="<?= htmlspecialchars($settings['site_logo'] ?? '') ?>" placeholder="/uploads/logo.png">
            <p class="form-hint">Leave empty to use the site title as text.</p>
        </div>

        <div class="form-group">
            <label class="form-label">Favicon URL</label>
            <input type="text" name="site_favicon" class="form-input" value="<?= htmlspecialchars($settings['site_favicon'] ?? '') ?>" placeholder="/uploads/favicon.ico">
        </div>

        <div class="form-group">
            <label class="form-label">Footer Text</label>
            <input type="text" name="footer_text" class="form-input" value="<?= htmlspecialchars($settings['footer_text'] ?? '') ?>" placeholder="© 2025 My Site. All rights reserved.">
        </div>
    </div>

    <div class="glass-card" style="max-width:700px;margin-top:24px">
        <h3 class="card-title">Custom Code</h3>

        <div class="form-group">
            <label class="form-label">Custom CSS</label>
            <textarea name="custom_css" class="form-input" rows="8" style="font-family:monospace;font-size:13px" placeholder="/* Your custom styles */"><?= htmlspecialchars($settings['custom_css'] ?? '') ?></textarea>
            <p class="form-hint">Added to the &lt;head&gt; of every page.</p>
        </div>

        <div class="form-group">
            <label class="form-label">Header Code</label>
            <textarea name="header_code" class="form-input" rows="5" style="font-family:monospace;font-size:13px" placeholder="<!-- Analytics, fonts, etc -->"><?= htmlspecialchars($settings['header_code'] ?? '') ?></textarea>
            <p class="form-hint">Injected before &lt;/head&gt;. Use for analytics, fonts, etc.</p>
        </div>

        <div class="form-group">
            <label class="form-label">Footer Code</label>
            <textarea name="footer_code" class="form-input" rows="5" style="font-family:monospace;font-size:13px" placeholder="<!-- Scripts, tracking, etc -->"><?= htmlspecialchars($settings['footer_code'] ?? '') ?></textarea>
            <p class="form-hint">Injected before &lt;/body&gt;.</p>
        </div>
    </div>

    <div style="margin-top:24px">
        <button type="submit" class="btn btn-primary">Save Appearance Settings</button>
    </div>
</form>

<style>
.settings-tabs{display:flex;gap:4px;margin-bottom:24px;flex-wrap:wrap}.settings-tab{padding:8px 16px;border-radius:8px 8px 0 0;color:var(--text-muted);text-decoration:none;font-size:14px;transition:all .2s;border:1px solid transparent;border-bottom:none}.settings-tab:hover{color:var(--text);background:var(--glass-bg)}.settings-tab.active{color:var(--text);background:var(--glass-bg);border-color:var(--glass-border);font-weight:600}
</style>
