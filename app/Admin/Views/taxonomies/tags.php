<div class="content-header">
    <div class="content-header-left">
        <h1>Tags</h1>
    </div>
</div>

<div class="taxonomy-layout">
    <div class="glass-card">
        <h3 class="card-title">Add New Tag</h3>
        <form method="post" action="<?= $adminUrl ?>/tags/store">
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
                <label>Description</label>
                <textarea name="description" rows="3" class="form-input"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Add Tag</button>
        </form>
    </div>

    <div class="glass-card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
            <h3 class="card-title" style="margin:0">All Tags</h3>
            <form class="search-bar" method="get" action="<?= $adminUrl ?>/tags" style="margin:0">
                <input type="text" name="s" placeholder="Search…" value="<?= htmlspecialchars($search ?? '') ?>" style="width:200px">
            </form>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Count</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                <tr><td colspan="4" style="text-align:center;padding:32px;color:var(--text-muted)">No tags found.</td></tr>
                <?php else: foreach ($items as $tag): ?>
                <tr>
                    <td><strong><a href="<?= $adminUrl ?>/tags/edit/<?= $tag['id'] ?>"><?= htmlspecialchars($tag['name']) ?></a></strong></td>
                    <td><code><?= htmlspecialchars($tag['slug']) ?></code></td>
                    <td><?= (int) $tag['count'] ?></td>
                    <td>
                        <a href="<?= $adminUrl ?>/tags/edit/<?= $tag['id'] ?>">Edit</a> |
                        <a href="<?= $adminUrl ?>/tags/delete/<?= $tag['id'] ?>" class="text-danger confirm-delete">Delete</a>
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
