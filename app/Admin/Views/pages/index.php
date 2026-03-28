<div class="content-header">
    <div class="content-header-left">
        <h1>Pages</h1>
        <a href="<?= $adminUrl ?>/pages/create" class="btn btn-primary">+ Add New</a>
    </div>
    <div class="content-header-right">
        <form class="search-bar" method="get" action="<?= $adminUrl ?>/pages">
            <input type="text" name="s" placeholder="Search pages…" value="<?= htmlspecialchars($search ?? '') ?>">
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
        'trash' => ['label' => 'Trash', 'count' => $counts['trash'] ?? 0],
    ];
    foreach ($statuses as $key => $info):
        if ($info['count'] === 0 && $key !== '' && $key !== $status) continue;
    ?>
    <a href="<?= $adminUrl ?>/pages<?= $key ? "?status={$key}" : '' ?>" 
       class="filter-tab <?= $status === $key ? 'active' : '' ?>">
        <?= $info['label'] ?> <span class="count">(<?= $info['count'] ?>)</span>
    </a>
    <?php endforeach; ?>
</div>

<div class="glass-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>Template</th>
                <th>Order</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($posts)): ?>
            <tr><td colspan="6" class="empty-state">
                <div class="empty-state-content">
                    <span style="font-size:48px">📄</span>
                    <h3>No pages found</h3>
                    <p>Create your first page to get started.</p>
                    <a href="<?= $adminUrl ?>/pages/create" class="btn btn-primary">Create Page</a>
                </div>
            </td></tr>
            <?php else: foreach ($posts as $post): ?>
            <tr>
                <td>
                    <strong>
                        <a href="<?= $adminUrl ?>/pages/edit/<?= $post['id'] ?>"><?= htmlspecialchars($post['title'] ?: '(no title)') ?></a>
                    </strong>
                    <?php if ($post['parent_id']): ?>
                        <span class="badge badge-default">Child page</span>
                    <?php endif; ?>
                    <div class="row-actions">
                        <a href="<?= $adminUrl ?>/pages/edit/<?= $post['id'] ?>">Edit</a> |
                        <?php if ($post['status'] === 'published'): ?>
                        <a href="<?= $siteUrl ?>/<?= $post['slug'] ?>" target="_blank">View</a> |
                        <?php endif; ?>
                        <a href="<?= $adminUrl ?>/pages/delete/<?= $post['id'] ?>" class="text-danger confirm-delete">
                            <?= $post['status'] === 'trash' ? 'Delete Permanently' : 'Trash' ?>
                        </a>
                    </div>
                </td>
                <td><?= htmlspecialchars($post['author_name'] ?? 'Unknown') ?></td>
                <td><?= ucfirst($post['page_template'] ?? 'default') ?></td>
                <td><?= (int) ($post['menu_order'] ?? 0) ?></td>
                <td><?= date('M j, Y', strtotime($post['created_at'])) ?></td>
                <td><span class="status-badge status-<?= $post['status'] ?>"><?= ucfirst($post['status']) ?></span></td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>

    <?php if ($pages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
            <?php $qs = array_filter(['status' => $status, 's' => $search, 'paged' => ($i > 1 ? $i : null)]); ?>
            <a href="<?= $adminUrl ?>/pages<?= $qs ? '?' . http_build_query($qs) : '' ?>" 
               class="page-link <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
