<div class="content-header">
    <div class="content-header-left">
        <h1>Edit Comment</h1>
        <a href="<?= $adminUrl ?>/comments" class="btn btn-default">← Back</a>
    </div>
</div>

<div class="glass-card" style="max-width:700px">
    <form method="post" action="<?= $adminUrl ?>/comments/update/<?= $comment['id'] ?>">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

        <div class="form-group">
            <label>In response to: <a href="<?= $adminUrl ?>/posts/edit/<?= $comment['post_id'] ?>"><?= htmlspecialchars($comment['post_title'] ?? 'Unknown') ?></a></label>
        </div>

        <div class="form-group">
            <label>Author Name</label>
            <input type="text" name="author_name" class="form-input" value="<?= htmlspecialchars($comment['author_name']) ?>">
        </div>
        <div class="form-group">
            <label>Author Email</label>
            <input type="email" name="author_email" class="form-input" value="<?= htmlspecialchars($comment['author_email']) ?>">
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status" class="form-input">
                <option value="approved" <?= $comment['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                <option value="pending" <?= $comment['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="spam" <?= $comment['status'] === 'spam' ? 'selected' : '' ?>>Spam</option>
            </select>
        </div>
        <div class="form-group">
            <label>Comment</label>
            <textarea name="content" rows="8" class="form-input"><?= htmlspecialchars($comment['content']) ?></textarea>
        </div>
        <div class="form-meta" style="margin-bottom:12px">
            <span>Submitted on: <?= date('M j, Y g:i A', strtotime($comment['created_at'])) ?></span>
            <span>IP: <?= htmlspecialchars($comment['author_ip'] ?? 'unknown') ?></span>
        </div>
        <div class="publish-actions">
            <button type="submit" class="btn btn-primary">Update Comment</button>
        </div>
    </form>
</div>

<style>
.form-meta { display: flex; flex-direction: column; gap: 4px; font-size: 12px; color: var(--text-muted); }
.publish-actions { display: flex; gap: 8px; justify-content: flex-end; padding-top: 12px; border-top: 1px solid var(--glass-border); }
</style>
