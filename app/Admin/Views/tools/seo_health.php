<div class="content-header">
    <div class="content-header-left">
        <h1>SEO Health Check</h1>
        <a href="<?= $adminUrl ?>/tools" style="color:var(--text-muted);text-decoration:none">&larr; Back to Tools</a>
    </div>
</div>

<div class="glass-card" style="max-width:900px">
    <div style="display:flex;gap:24px;margin-bottom:24px">
        <div class="stat-card" style="flex:1">
            <div class="stat-number"><?= $totalPublished ?></div>
            <div class="stat-label">Published Posts</div>
        </div>
        <div class="stat-card" style="flex:1">
            <div class="stat-number"><?= count($issues) ?></div>
            <div class="stat-label">Issues Found</div>
        </div>
        <div class="stat-card" style="flex:1">
            <div class="stat-number"><?= $totalPublished > 0 ? round((1 - count($issues) / max($totalPublished, 1)) * 100) : 100 ?>%</div>
            <div class="stat-label">Health Score</div>
        </div>
    </div>

    <?php if (empty($issues)): ?>
    <div style="text-align:center;padding:40px;color:var(--success)">
        <p style="font-size:48px;margin-bottom:16px">&#10003;</p>
        <h3>No issues found!</h3>
        <p style="color:var(--text-muted)">Your content looks great from an SEO perspective.</p>
    </div>
    <?php else: ?>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:40px"></th>
                <th>Category</th>
                <th>Details</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($issues as $issue): ?>
            <tr>
                <td>
                    <?php if ($issue['type'] === 'error'): ?>
                        <span style="color:var(--danger)" title="Error">&#9679;</span>
                    <?php elseif ($issue['type'] === 'warning'): ?>
                        <span style="color:var(--warning)" title="Warning">&#9679;</span>
                    <?php else: ?>
                        <span style="color:var(--info)" title="Info">&#9679;</span>
                    <?php endif; ?>
                </td>
                <td><?= $issue['category'] ?></td>
                <td><?= $issue['message'] ?></td>
                <td>
                    <?php if (!empty($issue['link'])): ?>
                    <a href="<?= $issue['link'] ?>" class="btn btn-sm">Fix</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
