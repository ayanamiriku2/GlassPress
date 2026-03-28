<div class="content-header"><div class="content-header-left"><h1>Advanced</h1></div></div>

<?php $tabs = ['general'=>'General','writing'=>'Writing','reading'=>'Reading','discussion'=>'Discussion','media'=>'Media','permalinks'=>'Permalinks','seo'=>'SEO','redirects'=>'Redirects','appearance'=>'Appearance','advanced'=>'Advanced']; $settingsPage = 'advanced'; ?>
<div class="settings-tabs"><?php foreach ($tabs as $key => $label): ?><a href="<?= $adminUrl ?>/settings/<?= $key ?>" class="settings-tab <?= $settingsPage === $key ? 'active' : '' ?>"><?= $label ?></a><?php endforeach; ?></div>

<form method="post" action="<?= $adminUrl ?>/settings/save">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    <input type="hidden" name="settings_group" value="advanced">

    <div class="glass-card" style="max-width:700px">
        <h3 class="card-title">Performance</h3>

        <div class="form-group">
            <label class="form-label">
                <input type="checkbox" name="enable_cache" value="1" <?= ($settings['enable_cache'] ?? '0') === '1' ? 'checked' : '' ?>>
                Enable page caching
            </label>
            <p class="form-hint">Caches rendered pages to files for faster load times.</p>
        </div>

        <div class="form-group">
            <label class="form-label">Cache Lifetime (seconds)</label>
            <input type="number" name="cache_lifetime" class="form-input" style="width:200px" value="<?= htmlspecialchars($settings['cache_lifetime'] ?? '3600') ?>" min="60" max="86400">
        </div>

        <div class="form-group">
            <label class="form-label">
                <input type="checkbox" name="enable_minify" value="1" <?= ($settings['enable_minify'] ?? '0') === '1' ? 'checked' : '' ?>>
                Minify HTML output
            </label>
            <p class="form-hint">Removes whitespace from HTML output to reduce page size.</p>
        </div>

        <div class="form-group">
            <label class="form-label">
                <input type="checkbox" name="enable_lazy_images" value="1" <?= ($settings['enable_lazy_images'] ?? '1') === '1' ? 'checked' : '' ?>>
                Lazy load images
            </label>
            <p class="form-hint">Adds loading="lazy" to images for better performance.</p>
        </div>
    </div>

    <div class="glass-card" style="max-width:700px;margin-top:24px">
        <h3 class="card-title">Security</h3>

        <div class="form-group">
            <label class="form-label">Login Attempt Limit</label>
            <input type="number" name="login_attempt_limit" class="form-input" style="width:200px" value="<?= htmlspecialchars($settings['login_attempt_limit'] ?? '5') ?>" min="3" max="20">
            <p class="form-hint">Lock accounts after this many failed login attempts.</p>
        </div>

        <div class="form-group">
            <label class="form-label">Lockout Duration (minutes)</label>
            <input type="number" name="lockout_duration" class="form-input" style="width:200px" value="<?= htmlspecialchars($settings['lockout_duration'] ?? '15') ?>" min="5" max="60">
        </div>

        <div class="form-group">
            <label class="form-label">
                <input type="checkbox" name="force_ssl" value="1" <?= ($settings['force_ssl'] ?? '0') === '1' ? 'checked' : '' ?>>
                Force SSL / HTTPS
            </label>
            <p class="form-hint">Redirect all HTTP requests to HTTPS.</p>
        </div>

        <div class="form-group">
            <label class="form-label">
                <input type="checkbox" name="disable_xmlrpc" value="1" <?= ($settings['disable_xmlrpc'] ?? '1') === '1' ? 'checked' : '' ?>>
                Disable XML-RPC
            </label>
        </div>
    </div>

    <div class="glass-card" style="max-width:700px;margin-top:24px">
        <h3 class="card-title">Content</h3>

        <div class="form-group">
            <label class="form-label">
                <input type="checkbox" name="enable_revisions" value="1" <?= ($settings['enable_revisions'] ?? '1') === '1' ? 'checked' : '' ?>>
                Enable post revisions
            </label>
        </div>

        <div class="form-group">
            <label class="form-label">Maximum Revisions per Post</label>
            <input type="number" name="max_revisions" class="form-input" style="width:200px" value="<?= htmlspecialchars($settings['max_revisions'] ?? '25') ?>" min="5" max="100">
        </div>

        <div class="form-group">
            <label class="form-label">
                <input type="checkbox" name="enable_trash" value="1" <?= ($settings['enable_trash'] ?? '1') === '1' ? 'checked' : '' ?>>
                Move deleted items to trash (instead of permanent delete)
            </label>
        </div>

        <div class="form-group">
            <label class="form-label">Trash Auto-Empty (days)</label>
            <input type="number" name="trash_days" class="form-input" style="width:200px" value="<?= htmlspecialchars($settings['trash_days'] ?? '30') ?>" min="0" max="365">
            <p class="form-hint">Set to 0 to disable auto-empty.</p>
        </div>
    </div>

    <div class="glass-card" style="max-width:700px;margin-top:24px">
        <h3 class="card-title">Maintenance</h3>

        <div class="form-group">
            <label class="form-label">
                <input type="checkbox" name="maintenance_mode" value="1" <?= ($settings['maintenance_mode'] ?? '0') === '1' ? 'checked' : '' ?>>
                Enable maintenance mode
            </label>
            <p class="form-hint">Only administrators can view the site. Visitors see a maintenance page.</p>
        </div>

        <div class="form-group">
            <label class="form-label">Maintenance Message</label>
            <textarea name="maintenance_message" class="form-input" rows="3"><?= htmlspecialchars($settings['maintenance_message'] ?? 'We are currently performing scheduled maintenance. Please check back soon.') ?></textarea>
        </div>
    </div>

    <div style="margin-top:24px">
        <button type="submit" class="btn btn-primary">Save Advanced Settings</button>
    </div>
</form>

<style>
.settings-tabs{display:flex;gap:4px;margin-bottom:24px;flex-wrap:wrap}.settings-tab{padding:8px 16px;border-radius:8px 8px 0 0;color:var(--text-muted);text-decoration:none;font-size:14px;transition:all .2s;border:1px solid transparent;border-bottom:none}.settings-tab:hover{color:var(--text);background:var(--glass-bg)}.settings-tab.active{color:var(--text);background:var(--glass-bg);border-color:var(--glass-border);font-weight:600}
</style>
