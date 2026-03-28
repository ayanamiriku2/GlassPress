<?php
$tabs = [
    'general' => 'General',
    'writing' => 'Writing',
    'reading' => 'Reading',
    'discussion' => 'Discussion',
    'media' => 'Media',
    'permalinks' => 'Permalinks',
    'seo' => 'SEO',
    'redirects' => 'Redirects',
    'appearance' => 'Appearance',
    'advanced' => 'Advanced',
];
?>

<div class="content-header">
    <div class="content-header-left">
        <h1><?= $pageTitle ?></h1>
    </div>
</div>

<div class="settings-tabs">
    <?php foreach ($tabs as $key => $label): ?>
    <a href="<?= $adminUrl ?>/settings/<?= $key ?>" 
       class="settings-tab <?= $settingsPage === $key ? 'active' : '' ?>"><?= $label ?></a>
    <?php endforeach; ?>
</div>

<div class="glass-card settings-form">
    <form method="post" action="<?= $adminUrl ?>/settings/save">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
        <input type="hidden" name="_settings_page" value="<?= $settingsPage ?>">

        <div class="form-group">
            <label>Site Title</label>
            <input type="text" name="site_title" class="form-input" value="<?= htmlspecialchars($settings->get('site_title', '')) ?>">
        </div>

        <div class="form-group">
            <label>Tagline</label>
            <input type="text" name="site_tagline" class="form-input" value="<?= htmlspecialchars($settings->get('site_tagline', '')) ?>">
            <p class="form-hint">A short description of your site.</p>
        </div>

        <div class="form-group">
            <label>Site URL</label>
            <input type="url" name="site_url" class="form-input" value="<?= htmlspecialchars($settings->get('site_url', '')) ?>">
        </div>

        <div class="form-group">
            <label>Admin Email</label>
            <input type="email" name="admin_email" class="form-input" value="<?= htmlspecialchars($settings->get('admin_email', '')) ?>">
        </div>

        <div class="form-group">
            <label>Timezone</label>
            <select name="timezone" class="form-input">
                <?php
                $currentTz = $settings->get('timezone', 'UTC');
                $tzList = ['UTC', 'America/New_York', 'America/Chicago', 'America/Denver', 'America/Los_Angeles', 'America/Toronto', 'Europe/London', 'Europe/Paris', 'Europe/Berlin', 'Europe/Amsterdam', 'Asia/Tokyo', 'Asia/Shanghai', 'Asia/Kolkata', 'Asia/Dubai', 'Australia/Sydney', 'Pacific/Auckland'];
                foreach ($tzList as $tz):
                ?>
                <option value="<?= $tz ?>" <?= $currentTz === $tz ? 'selected' : '' ?>><?= $tz ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Date Format</label>
            <select name="date_format" class="form-input">
                <?php
                $currentFmt = $settings->get('date_format', 'F j, Y');
                $formats = ['F j, Y' => 'January 1, 2025', 'Y-m-d' => '2025-01-01', 'm/d/Y' => '01/01/2025', 'd/m/Y' => '01/01/2025', 'M j, Y' => 'Jan 1, 2025'];
                foreach ($formats as $fmt => $example):
                ?>
                <option value="<?= $fmt ?>" <?= $currentFmt === $fmt ? 'selected' : '' ?>><?= $example ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Time Format</label>
            <select name="time_format" class="form-input">
                <?php
                $currentTimeFmt = $settings->get('time_format', 'g:i a');
                $timeFormats = ['g:i a' => '1:30 pm', 'g:i A' => '1:30 PM', 'H:i' => '13:30'];
                foreach ($timeFormats as $tf => $texample):
                ?>
                <option value="<?= $tf ?>" <?= $currentTimeFmt === $tf ? 'selected' : '' ?>><?= $texample ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Language</label>
            <select name="language" class="form-input">
                <?php
                $currentLang = $settings->get('language', 'en');
                $langs = ['en' => 'English', 'es' => 'Spanish', 'fr' => 'French', 'de' => 'German', 'it' => 'Italian', 'pt' => 'Portuguese', 'nl' => 'Dutch', 'ja' => 'Japanese', 'zh' => 'Chinese', 'ko' => 'Korean'];
                foreach ($langs as $code => $label):
                ?>
                <option value="<?= $code ?>" <?= $currentLang === $code ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
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
