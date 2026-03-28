<div class="content-header">
    <div class="content-header-left">
        <h1>Users</h1>
        <a href="<?= $adminUrl ?>/users/create" class="btn btn-primary">+ Add New</a>
    </div>
    <div class="content-header-right">
        <form class="search-bar" method="get" action="<?= $adminUrl ?>/users">
            <input type="text" name="s" placeholder="Search users…" value="<?= htmlspecialchars($search ?? '') ?>">
            <button type="submit">Search</button>
        </form>
    </div>
</div>

<div class="filter-tabs">
    <?php
    $roles = ['' => 'All', 'administrator' => 'Admin', 'editor' => 'Editor', 'author' => 'Author', 'contributor' => 'Contributor', 'subscriber' => 'Subscriber'];
    $allCount = array_sum($roleCounts ?? []);
    foreach ($roles as $key => $label):
        $count = $key === '' ? $allCount : ($roleCounts[$key] ?? 0);
        if ($count === 0 && $key !== '' && $key !== $role) continue;
    ?>
    <a href="<?= $adminUrl ?>/users<?= $key ? "?role={$key}" : '' ?>" 
       class="filter-tab <?= $role === $key ? 'active' : '' ?>">
        <?= $label ?> <span class="count">(<?= $count ?>)</span>
    </a>
    <?php endforeach; ?>
</div>

<div class="glass-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Username</th>
                <th>Display Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Joined</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
            <tr><td colspan="6" class="empty-state">
                <div class="empty-state-content">
                    <span style="font-size:48px">👤</span>
                    <h3>No users found</h3>
                </div>
            </td></tr>
            <?php else: foreach ($users as $u): ?>
            <tr>
                <td>
                    <strong><a href="<?= $adminUrl ?>/users/edit/<?= $u['id'] ?>"><?= htmlspecialchars($u['username']) ?></a></strong>
                    <div class="row-actions">
                        <a href="<?= $adminUrl ?>/users/edit/<?= $u['id'] ?>">Edit</a>
                        <?php if ($u['id'] != $user['id']): ?>
                        | <a href="<?= $adminUrl ?>/users/delete/<?= $u['id'] ?>" class="text-danger confirm-delete" data-confirm="Delete this user? Their content will be reassigned to you.">Delete</a>
                        <?php endif; ?>
                    </div>
                </td>
                <td><?= htmlspecialchars($u['display_name'] ?? $u['username']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><span class="badge badge-<?= $u['role'] === 'administrator' ? 'primary' : 'default' ?>"><?= ucfirst($u['role']) ?></span></td>
                <td><span class="status-badge status-<?= $u['status'] === 'active' ? 'published' : 'draft' ?>"><?= ucfirst($u['status']) ?></span></td>
                <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>

    <?php if ($pages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
            <?php $qs = array_filter(['role' => $role, 's' => $search, 'paged' => ($i > 1 ? $i : null)]); ?>
            <a href="<?= $adminUrl ?>/users<?= $qs ? '?' . http_build_query($qs) : '' ?>" 
               class="page-link <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
