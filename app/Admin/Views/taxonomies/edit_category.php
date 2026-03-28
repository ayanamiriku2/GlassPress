<div class="content-header">
    <div class="content-header-left">
        <h1>Edit Category</h1>
        <a href="<?= $adminUrl ?>/categories" class="btn btn-default">← Back</a>
    </div>
</div>

<div class="glass-card" style="max-width:600px">
    <form method="post" action="<?= $adminUrl ?>/categories/update/<?= $item['id'] ?>">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" class="form-input" value="<?= htmlspecialchars($item['name']) ?>" required>
        </div>
        <div class="form-group">
            <label>Slug</label>
            <input type="text" name="slug" class="form-input" value="<?= htmlspecialchars($item['slug']) ?>">
        </div>
        <div class="form-group">
            <label>Parent Category</label>
            <select name="parent_id" class="form-input">
                <option value="0">None</option>
                <?php foreach ($allCategories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= ($item['parent_id'] ?? 0) == $cat['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="3" class="form-input"><?= htmlspecialchars($item['description'] ?? '') ?></textarea>
        </div>
        <div class="publish-actions">
            <button type="submit" class="btn btn-primary">Update Category</button>
        </div>
    </form>
</div>

<style>
.publish-actions { display: flex; gap: 8px; justify-content: flex-end; padding-top: 12px; border-top: 1px solid var(--glass-border); }
</style>
