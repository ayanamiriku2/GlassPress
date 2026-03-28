<div class="content-header">
    <div class="content-header-left">
        <h1>404 Error Log</h1>
        <a href="<?= $adminUrl ?>/tools" style="color:var(--text-muted);text-decoration:none">&larr; Back to Tools</a>
    </div>
    <div class="content-header-right">
        <span style="color:var(--text-muted)"><?= $total ?> total entries</span>
    </div>
</div>

<div class="glass-card" style="max-width:1000px">
    <?php if (empty($entries)): ?>
    <p style="text-align:center;padding:40px;color:var(--text-muted)">No 404 errors logged yet.</p>
    <?php else: ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>URL</th>
                <th>Referrer</th>
                <th>Hits</th>
                <th>Last Hit</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($entries as $e): ?>
            <tr>
                <td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                    <code style="font-size:12px"><?= htmlspecialchars($e['url']) ?></code>
                </td>
                <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--text-muted);font-size:12px">
                    <?= $e['referrer'] ? htmlspecialchars($e['referrer']) : '—' ?>
                </td>
                <td><span class="badge"><?= (int) $e['hit_count'] ?></span></td>
                <td style="font-size:13px;color:var(--text-muted)"><?= date('M j, Y g:ia', strtotime($e['last_hit'])) ?></td>
                <td>
                    <a href="<?= $adminUrl ?>/settings/redirects?source=<?= urlencode($e['url']) ?>" class="btn btn-sm">Create Redirect</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?>" class="btn btn-sm">&laquo; Previous</a>
        <?php endif; ?>
        <span style="color:var(--text-muted);padding:0 12px">Page <?= $page ?> of <?= $totalPages ?></span>
        <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page + 1 ?>" class="btn btn-sm">Next &raquo;</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>
