<div class="content-header">
    <div class="content-header-left">
        <h1>Comments</h1>
    </div>
</div>

<div class="filter-tabs">
    <?php
    $allCount = array_sum($counts ?? []) - ($counts['spam'] ?? 0) - ($counts['trash'] ?? 0);
    $statuses = [
        '' => ['label' => 'All', 'count' => $allCount],
        'pending' => ['label' => 'Pending', 'count' => $counts['pending'] ?? 0],
        'approved' => ['label' => 'Approved', 'count' => $counts['approved'] ?? 0],
        'spam' => ['label' => 'Spam', 'count' => $counts['spam'] ?? 0],
        'trash' => ['label' => 'Trash', 'count' => $counts['trash'] ?? 0],
    ];
    foreach ($statuses as $key => $info):
        if ($info['count'] === 0 && $key !== '' && $key !== $status) continue;
    ?>
    <a href="<?= $adminUrl ?>/comments<?= $key ? "?status={$key}" : '' ?>" 
       class="filter-tab <?= $status === $key ? 'active' : '' ?>">
        <?= $info['label'] ?> <span class="count">(<?= $info['count'] ?>)</span>
    </a>
    <?php endforeach; ?>
</div>

<div class="glass-card">
    <form method="post" action="<?= $adminUrl ?>/comments/bulk" id="comments-bulk-form">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

        <div class="bulk-actions">
            <select name="bulk_action">
                <option value="">Bulk Actions</option>
                <option value="approve">Approve</option>
                <option value="unapprove">Unapprove</option>
                <option value="spam">Mark as Spam</option>
                <option value="trash">Move to Trash</option>
            </select>
            <button type="submit" class="btn btn-default btn-sm">Apply</button>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:30px"><input type="checkbox" id="select-all"></th>
                    <th>Author</th>
                    <th>Comment</th>
                    <th>In Response To</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($comments)): ?>
                <tr><td colspan="5" class="empty-state">
                    <div class="empty-state-content">
                        <span style="font-size:48px">💬</span>
                        <h3>No comments found</h3>
                    </div>
                </td></tr>
                <?php else: foreach ($comments as $comment): ?>
                <tr class="<?= $comment['status'] === 'pending' ? 'row-highlight' : '' ?>">
                    <td><input type="checkbox" name="comment_ids[]" value="<?= $comment['id'] ?>" class="row-check"></td>
                    <td>
                        <strong><?= htmlspecialchars($comment['author_name']) ?></strong><br>
                        <small style="color:var(--text-muted)">
                            <?= htmlspecialchars($comment['author_email']) ?>
                        </small>
                    </td>
                    <td>
                        <div class="comment-content">
                            <?= nl2br(htmlspecialchars(mb_strimwidth(strip_tags($comment['content']), 0, 200, '…'))) ?>
                        </div>
                        <span class="status-badge status-<?= $comment['status'] ?>"><?= ucfirst($comment['status']) ?></span>
                        <div class="row-actions">
                            <?php if ($comment['status'] !== 'approved'): ?>
                            <a href="<?= $adminUrl ?>/comments/approve/<?= $comment['id'] ?>">Approve</a> |
                            <?php endif; ?>
                            <?php if ($comment['status'] !== 'pending'): ?>
                            <a href="<?= $adminUrl ?>/comments/unapprove/<?= $comment['id'] ?>">Unapprove</a> |
                            <?php endif; ?>
                            <a href="<?= $adminUrl ?>/comments/edit/<?= $comment['id'] ?>">Edit</a> |
                            <a href="#" onclick="event.preventDefault();document.getElementById('reply-<?= $comment['id'] ?>').classList.toggle('hidden')">Reply</a> |
                            <?php if ($comment['status'] !== 'spam'): ?>
                            <a href="<?= $adminUrl ?>/comments/spam/<?= $comment['id'] ?>">Spam</a> |
                            <?php endif; ?>
                            <?php if ($comment['status'] === 'trash'): ?>
                            <a href="<?= $adminUrl ?>/comments/delete/<?= $comment['id'] ?>" class="text-danger confirm-delete">Delete Permanently</a>
                            <?php else: ?>
                            <a href="<?= $adminUrl ?>/comments/trash/<?= $comment['id'] ?>" class="text-danger">Trash</a>
                            <?php endif; ?>
                        </div>

                        <!-- Inline reply form -->
                        <div id="reply-<?= $comment['id'] ?>" class="comment-reply-form hidden">
                            <form method="post" action="<?= $adminUrl ?>/comments/reply/<?= $comment['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                <textarea name="reply_content" rows="3" class="form-input" placeholder="Write a reply…"></textarea>
                                <div style="text-align:right;margin-top:8px">
                                    <button type="button" class="btn btn-default btn-sm" onclick="this.closest('.comment-reply-form').classList.add('hidden')">Cancel</button>
                                    <button type="submit" class="btn btn-primary btn-sm">Reply</button>
                                </div>
                            </form>
                        </div>
                    </td>
                    <td>
                        <?php if ($comment['post_title']): ?>
                        <a href="<?= $adminUrl ?>/posts/edit/<?= $comment['post_id'] ?>"><?= htmlspecialchars($comment['post_title']) ?></a>
                        <?php else: ?>
                        <em>(deleted post)</em>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span title="<?= $comment['created_at'] ?>"><?= date('M j, Y', strtotime($comment['created_at'])) ?></span><br>
                        <small><?= date('g:i A', strtotime($comment['created_at'])) ?></small>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </form>

    <?php if ($pages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
            <?php $qs = array_filter(['status' => $status, 'paged' => ($i > 1 ? $i : null)]); ?>
            <a href="<?= $adminUrl ?>/comments<?= $qs ? '?' . http_build_query($qs) : '' ?>" 
               class="page-link <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<style>
.comment-content { margin-bottom: 8px; line-height: 1.5; }
.comment-reply-form { margin-top: 12px; padding: 12px; background: var(--glass-bg); border-radius: 8px; border: 1px solid var(--glass-border); }
.row-highlight { background: rgba(99, 102, 241, .05); }
.hidden { display: none !important; }
</style>
