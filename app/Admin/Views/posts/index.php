<div class="content-header">
    <div class="content-header-left">
        <h1>Posts</h1>
        <a href="<?= $adminUrl ?>/posts/create" class="btn btn-primary">+ Add New</a>
    </div>
    <div class="content-header-right">
        <form class="search-bar" method="get" action="<?= $adminUrl ?>/posts">
            <input type="text" name="s" placeholder="Search posts…" value="<?= htmlspecialchars($search ?? '') ?>">
            <button type="submit">Search</button>
        </form>
    </div>
</div>

<div class="filter-tabs">
    <?php
    $allCount = array_sum($counts ?? []) - ($counts['trash'] ?? 0);
    $statuses = [
        '' => ['label' => 'All', 'count' => $allCount],
        'published' => ['label' => 'Published', 'count' => $counts['published'] ?? 0],
        'draft' => ['label' => 'Drafts', 'count' => $counts['draft'] ?? 0],
        'pending' => ['label' => 'Pending', 'count' => $counts['pending'] ?? 0],
        'scheduled' => ['label' => 'Scheduled', 'count' => $counts['scheduled'] ?? 0],
        'trash' => ['label' => 'Trash', 'count' => $counts['trash'] ?? 0],
    ];
    foreach ($statuses as $key => $info):
        if ($info['count'] === 0 && $key !== '' && $key !== $status) continue;
    ?>
    <a href="<?= $adminUrl ?>/posts<?= $key ? "?status={$key}" : '' ?>" 
       class="filter-tab <?= $status === $key ? 'active' : '' ?>">
        <?= $info['label'] ?> <span class="count">(<?= $info['count'] ?>)</span>
    </a>
    <?php endforeach; ?>
</div>

<div class="glass-card">
    <form method="post" action="<?= $adminUrl ?>/posts/bulk" id="bulk-form">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

        <div class="bulk-actions">
            <select name="bulk_action">
                <option value="">Bulk Actions</option>
                <?php if ($status !== 'trash'): ?>
                    <option value="publish">Publish</option>
                    <option value="draft">Move to Draft</option>
                    <option value="trash">Move to Trash</option>
                <?php else: ?>
                    <option value="draft">Restore</option>
                <?php endif; ?>
            </select>
            <button type="submit" class="btn btn-default btn-sm">Apply</button>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:30px"><input type="checkbox" id="select-all"></th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Categories</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($posts)): ?>
                <tr><td colspan="6" class="empty-state">
                    <div class="empty-state-content">
                        <span style="font-size:48px">📝</span>
                        <h3>No posts found</h3>
                        <p>Create your first post to get started.</p>
                        <a href="<?= $adminUrl ?>/posts/create" class="btn btn-primary">Create Post</a>
                    </div>
                </td></tr>
                <?php else: foreach ($posts as $post): ?>
                <tr>
                    <td><input type="checkbox" name="post_ids[]" value="<?= $post['id'] ?>" class="row-check"></td>
                    <td>
                        <strong>
                            <a href="<?= $adminUrl ?>/posts/edit/<?= $post['id'] ?>"><?= htmlspecialchars($post['title'] ?: '(no title)') ?></a>
                        </strong>
                        <?php if ($post['is_sticky']): ?><span class="badge badge-info">Sticky</span><?php endif; ?>
                        <div class="row-actions">
                            <a href="<?= $adminUrl ?>/posts/edit/<?= $post['id'] ?>">Edit</a> |
                            <a href="<?= $adminUrl ?>/posts/duplicate/<?= $post['id'] ?>">Duplicate</a> |
                            <a href="<?= $adminUrl ?>/posts/revisions/<?= $post['id'] ?>">Revisions</a> |
                            <?php if ($post['status'] === 'published'): ?>
                            <a href="<?= $siteUrl ?>/<?= $post['slug'] ?>" target="_blank">View</a> |
                            <?php endif; ?>
                            <a href="<?= $adminUrl ?>/posts/delete/<?= $post['id'] ?>" class="text-danger confirm-delete" data-confirm="Are you sure you want to <?= $post['status'] === 'trash' ? 'permanently delete' : 'trash' ?> this post?">
                                <?= $post['status'] === 'trash' ? 'Delete Permanently' : 'Trash' ?>
                            </a>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($post['author_name'] ?? 'Unknown') ?></td>
                    <td>
                        <?php
                        // We don't have categories pre-loaded, so just show a dash for now
                        echo '—';
                        ?>
                    </td>
                    <td>
                        <span title="<?= $post['created_at'] ?>">
                            <?= date('M j, Y', strtotime($post['created_at'])) ?>
                        </span>
                        <?php if ($post['published_at']): ?>
                        <br><small>Published: <?= date('M j, Y', strtotime($post['published_at'])) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><span class="status-badge status-<?= $post['status'] ?>"><?= ucfirst($post['status']) ?></span></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </form>

    <?php if ($pages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
            <?php
            $params = array_filter(['status' => $status, 's' => $search, 'paged' => ($i > 1 ? $i : null)]);
            $qs = $params ? '?' . http_build_query($params) : '';
            ?>
            <a href="<?= $adminUrl ?>/posts<?= $qs ?>" 
               class="page-link <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
