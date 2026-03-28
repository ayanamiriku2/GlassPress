<div class="content-header"><div class="content-header-left"><h1>Tools</h1></div></div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:24px">
    <div class="glass-card">
        <h3 class="card-title">SEO Health Check</h3>
        <p style="color:var(--text-muted);margin-bottom:16px">Scan your content for common SEO issues like missing meta descriptions, short titles, and missing images.</p>
        <a href="<?= $adminUrl ?>/tools/seo-health" class="btn btn-primary">Run Check</a>
    </div>

    <div class="glass-card">
        <h3 class="card-title">404 Error Log</h3>
        <p style="color:var(--text-muted);margin-bottom:16px">View URLs that returned 404 errors. Create redirects for common broken links.</p>
        <a href="<?= $adminUrl ?>/tools/404-log" class="btn btn-primary">View Log</a>
    </div>

    <div class="glass-card">
        <h3 class="card-title">Clear Cache</h3>
        <p style="color:var(--text-muted);margin-bottom:16px">Clear all cached data including page caches and compiled views.</p>
        <form method="post" action="<?= $adminUrl ?>/tools/clear-cache">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <button type="submit" class="btn btn-warning" onclick="return confirm('Clear all caches?')">Clear Cache</button>
        </form>
    </div>

    <div class="glass-card">
        <h3 class="card-title">Export Content</h3>
        <p style="color:var(--text-muted);margin-bottom:16px">Download all posts, pages, comments, menus, and SEO data as a JSON file.</p>
        <a href="<?= $adminUrl ?>/tools/export" class="btn btn-primary">Download Export</a>
    </div>

    <div class="glass-card">
        <h3 class="card-title">Import Content</h3>
        <p style="color:var(--text-muted);margin-bottom:16px">Import content from a GlassPress export file. Existing content will not be overwritten.</p>
        <form method="post" action="<?= $adminUrl ?>/tools/import" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <input type="file" name="import_file" accept=".json" class="form-input" style="margin-bottom:12px" required>
            <button type="submit" class="btn btn-primary" onclick="return confirm('Import content from this file?')">Import</button>
        </form>
    </div>

    <div class="glass-card">
        <h3 class="card-title">System Information</h3>
        <p style="color:var(--text-muted);margin-bottom:16px">View server configuration, PHP version, database info, and content statistics.</p>
        <a href="<?= $adminUrl ?>/tools/system-info" class="btn btn-primary">View Info</a>
    </div>
</div>
