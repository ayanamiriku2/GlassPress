<div class="content-header">
    <div class="content-header-left">
        <h1><?= htmlspecialchars($pageTitle) ?></h1>
        <a href="<?= $adminUrl ?>/posts/edit/<?= $post['id'] ?>" class="btn btn-default">← Back to Editor</a>
    </div>
</div>

<div class="glass-card">
    <?php if (empty($revisions)): ?>
    <div class="empty-state">
        <div class="empty-state-content">
            <span style="font-size:48px">📋</span>
            <h3>No revisions yet</h3>
            <p>Revisions are created automatically when you save or autosave.</p>
        </div>
    </div>
    <?php else: ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Author</th>
                <th>Type</th>
                <th>Title</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($revisions as $rev): ?>
            <tr>
                <td><?= date('M j, Y g:i A', strtotime($rev['created_at'])) ?></td>
                <td><?= htmlspecialchars($rev['author_name'] ?? 'Unknown') ?></td>
                <td><span class="status-badge status-<?= $rev['revision_type'] === 'autosave' ? 'draft' : 'published' ?>"><?= ucfirst($rev['revision_type']) ?></span></td>
                <td><?= htmlspecialchars($rev['title'] ?: '(no title)') ?></td>
                <td>
                    <form method="post" action="<?= $adminUrl ?>/posts/revisions/restore/<?= $rev['id'] ?>" style="display:inline">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <button type="submit" class="btn btn-sm btn-default" onclick="return confirm('Restore this revision? Current content will be overwritten.')">Restore</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
