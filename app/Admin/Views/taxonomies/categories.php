<div class="content-header">
    <div class="content-header-left">
        <h1>Categories</h1>
    </div>
</div>

<div class="taxonomy-layout">
    <div class="glass-card">
        <h3 class="card-title">Add New Category</h3>
        <form method="post" action="<?= $adminUrl ?>/categories/store">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-input" required>
            </div>
            <div class="form-group">
                <label>Slug</label>
                <input type="text" name="slug" class="form-input" placeholder="Auto-generated from name">
            </div>
            <div class="form-group">
                <label>Parent Category</label>
                <select name="parent_id" class="form-input">
                    <option value="0">None</option>
                    <?php foreach ($items as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3" class="form-input"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Add Category</button>
        </form>
    </div>

    <div class="glass-card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
            <h3 class="card-title" style="margin:0">All Categories</h3>
            <form class="search-bar" method="get" action="<?= $adminUrl ?>/categories" style="margin:0">
                <input type="text" name="s" placeholder="Search…" value="<?= htmlspecialchars($search ?? '') ?>" style="width:200px">
            </form>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Description</th>
                    <th>Count</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                <tr><td colspan="5" style="text-align:center;padding:32px;color:var(--text-muted)">No categories found.</td></tr>
                <?php else: foreach ($items as $cat): ?>
                <tr>
                    <td>
                        <strong><a href="<?= $adminUrl ?>/categories/edit/<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></a></strong>
                        <?php if ($cat['parent_id']): ?><br><small style="color:var(--text-muted)">— child</small><?php endif; ?>
                    </td>
                    <td><code><?= htmlspecialchars($cat['slug']) ?></code></td>
                    <td><?= htmlspecialchars(mb_strimwidth($cat['description'] ?? '', 0, 60, '…')) ?></td>
                    <td><?= (int) $cat['count'] ?></td>
                    <td>
                        <a href="<?= $adminUrl ?>/categories/edit/<?= $cat['id'] ?>">Edit</a> |
                        <a href="<?= $adminUrl ?>/categories/delete/<?= $cat['id'] ?>" class="text-danger confirm-delete">Delete</a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.taxonomy-layout { display: grid; grid-template-columns: 340px 1fr; gap: 24px; align-items: start; }
@media (max-width: 900px) { .taxonomy-layout { grid-template-columns: 1fr; } }
</style>
