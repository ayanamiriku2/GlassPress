<div class="content-header">
    <div class="content-header-left">
        <h1>System Information</h1>
        <a href="<?= $adminUrl ?>/tools" style="color:var(--text-muted);text-decoration:none">&larr; Back to Tools</a>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;max-width:1000px">
    <div class="glass-card">
        <h3 class="card-title">Server Environment</h3>
        <table class="data-table" style="font-size:13px">
            <tbody>
                <tr><td style="font-weight:600;width:40%">PHP Version</td><td><?= htmlspecialchars($info['php_version']) ?></td></tr>
                <tr><td style="font-weight:600">MySQL Version</td><td><?= htmlspecialchars($info['mysql_version']) ?></td></tr>
                <tr><td style="font-weight:600">Web Server</td><td><?= htmlspecialchars($info['server_software']) ?></td></tr>
                <tr><td style="font-weight:600">Document Root</td><td style="word-break:break-all"><?= htmlspecialchars($info['document_root']) ?></td></tr>
                <tr><td style="font-weight:600">GlassPress Root</td><td style="word-break:break-all"><?= htmlspecialchars($info['glasspress_root']) ?></td></tr>
                <tr><td style="font-weight:600">Disk Free</td><td><?= $info['disk_free'] ?></td></tr>
            </tbody>
        </table>
    </div>

    <div class="glass-card">
        <h3 class="card-title">PHP Configuration</h3>
        <table class="data-table" style="font-size:13px">
            <tbody>
                <tr><td style="font-weight:600;width:40%">Upload Max</td><td><?= htmlspecialchars($info['max_upload']) ?></td></tr>
                <tr><td style="font-weight:600">Post Max Size</td><td><?= htmlspecialchars($info['post_max_size']) ?></td></tr>
                <tr><td style="font-weight:600">Memory Limit</td><td><?= htmlspecialchars($info['memory_limit']) ?></td></tr>
                <tr><td style="font-weight:600">Max Execution Time</td><td><?= htmlspecialchars($info['max_execution_time']) ?></td></tr>
                <tr><td style="font-weight:600">GD Support</td><td><?= $info['gd_support'] ?></td></tr>
                <tr><td style="font-weight:600">cURL Support</td><td><?= $info['curl_support'] ?></td></tr>
                <tr><td style="font-weight:600">OpenSSL Support</td><td><?= $info['openssl_support'] ?></td></tr>
                <tr><td style="font-weight:600">mbstring Support</td><td><?= $info['mbstring_support'] ?></td></tr>
            </tbody>
        </table>
    </div>

    <div class="glass-card" style="grid-column:span 2">
        <h3 class="card-title">Content Statistics</h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:16px">
            <?php foreach ($stats as $label => $count): ?>
            <div class="stat-card">
                <div class="stat-number"><?= number_format($count) ?></div>
                <div class="stat-label"><?= ucfirst($label) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="glass-card" style="grid-column:span 2">
        <h3 class="card-title">Loaded PHP Extensions</h3>
        <p style="font-size:12px;color:var(--text-muted);line-height:1.8;word-spacing:4px">
            <?= htmlspecialchars($info['php_extensions']) ?>
        </p>
    </div>
</div>
