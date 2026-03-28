<div class="content-header">
    <div class="content-header-left">
        <h1>Menus</h1>
    </div>
</div>

<div class="menu-layout">
    <!-- Left: Add items -->
    <div class="menu-sidebar">
        <?php if ($selectedMenu): ?>
        <!-- Custom Links -->
        <div class="glass-card">
            <h3 class="card-title">Custom Links</h3>
            <form method="post" action="<?= $adminUrl ?>/menus/add-item">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="menu_id" value="<?= $selectedMenu['id'] ?>">
                <input type="hidden" name="type" value="custom">
                <div class="form-group">
                    <label>URL</label>
                    <input type="url" name="url" class="form-input" placeholder="https://" required>
                </div>
                <div class="form-group">
                    <label>Link Text</label>
                    <input type="text" name="title" class="form-input" required>
                </div>
                <button type="submit" class="btn btn-default btn-sm">Add to Menu</button>
            </form>
        </div>

        <!-- Pages -->
        <div class="glass-card">
            <h3 class="card-title">Pages</h3>
            <form method="post" action="<?= $adminUrl ?>/menus/add-item">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="menu_id" value="<?= $selectedMenu['id'] ?>">
                <input type="hidden" name="type" value="page">
                <div class="taxonomy-checklist" style="max-height:200px;overflow-y:auto">
                    <?php foreach ($pages as $p): ?>
                    <label class="checkbox-label">
                        <input type="checkbox" name="page_ids[]" value="<?= $p['id'] ?>">
                        <?= htmlspecialchars($p['title']) ?>
                    </label>
                    <?php endforeach; ?>
                    <?php if (empty($pages)): ?>
                    <p class="form-hint">No published pages.</p>
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn btn-default btn-sm" style="margin-top:8px">Add to Menu</button>
            </form>
        </div>

        <!-- Categories -->
        <div class="glass-card">
            <h3 class="card-title">Categories</h3>
            <form method="post" action="<?= $adminUrl ?>/menus/add-item">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="menu_id" value="<?= $selectedMenu['id'] ?>">
                <input type="hidden" name="type" value="category">
                <div class="taxonomy-checklist" style="max-height:200px;overflow-y:auto">
                    <?php foreach ($categories as $cat): ?>
                    <label class="checkbox-label">
                        <input type="checkbox" name="category_ids[]" value="<?= $cat['id'] ?>">
                        <?= htmlspecialchars($cat['name']) ?>
                    </label>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="btn btn-default btn-sm" style="margin-top:8px">Add to Menu</button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <!-- Right: Menu editor -->
    <div class="menu-editor">
        <!-- Menu selector / create -->
        <div class="glass-card">
            <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
                <label style="font-weight:600">Select a menu to edit:</label>
                <?php if (!empty($menus)): ?>
                <select onchange="window.location='<?= $adminUrl ?>/menus?menu='+this.value" class="form-input" style="width:auto">
                    <?php foreach ($menus as $m): ?>
                    <option value="<?= $m['id'] ?>" <?= ($selectedMenu['id'] ?? 0) == $m['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($m['name']) ?> (<?= $m['location'] ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>
                <span style="color:var(--text-muted)">or</span>
                <form method="post" action="<?= $adminUrl ?>/menus/store" style="display:flex;gap:8px;align-items:center">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <input type="text" name="name" class="form-input" placeholder="New menu name" style="width:200px" required>
                    <select name="location" class="form-input" style="width:auto">
                        <option value="primary">Primary</option>
                        <option value="footer">Footer</option>
                        <option value="sidebar">Sidebar</option>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">Create Menu</button>
                </form>
            </div>
        </div>

        <?php if ($selectedMenu): ?>
        <form method="post" action="<?= $adminUrl ?>/menus/update/<?= $selectedMenu['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

            <div class="glass-card">
                <div style="display:flex;gap:12px;align-items:center;margin-bottom:16px">
                    <label style="font-weight:600">Menu Name:</label>
                    <input type="text" name="name" class="form-input" value="<?= htmlspecialchars($selectedMenu['name']) ?>" style="flex:1">
                    <select name="location" class="form-input" style="width:auto">
                        <option value="primary" <?= $selectedMenu['location'] === 'primary' ? 'selected' : '' ?>>Primary</option>
                        <option value="footer" <?= $selectedMenu['location'] === 'footer' ? 'selected' : '' ?>>Footer</option>
                        <option value="sidebar" <?= $selectedMenu['location'] === 'sidebar' ? 'selected' : '' ?>>Sidebar</option>
                    </select>
                </div>

                <div class="menu-items-list" id="menu-items">
                    <?php if (empty($menuItems)): ?>
                    <div class="empty-state" style="padding:40px">
                        <div class="empty-state-content">
                            <span style="font-size:48px">📜</span>
                            <h3>Empty menu</h3>
                            <p>Add items from the sidebar panels.</p>
                        </div>
                    </div>
                    <?php else: foreach ($menuItems as $i => $item): ?>
                    <div class="menu-item-card" draggable="true">
                        <div class="menu-item-header">
                            <span class="menu-item-drag">☰</span>
                            <span class="menu-item-label"><?= htmlspecialchars($item['title']) ?></span>
                            <span class="menu-item-type badge badge-default"><?= $item['type'] ?></span>
                            <button type="button" class="menu-item-toggle" onclick="this.closest('.menu-item-card').classList.toggle('open')">▼</button>
                        </div>
                        <div class="menu-item-body">
                            <input type="hidden" name="item_type[]" value="<?= $item['type'] ?>">
                            <input type="hidden" name="item_object_id[]" value="<?= $item['object_id'] ?? '' ?>">
                            <input type="hidden" name="item_parent[]" value="">
                            <div class="form-group">
                                <label>Navigation Label</label>
                                <input type="text" name="item_title[]" class="form-input" value="<?= htmlspecialchars($item['title']) ?>">
                            </div>
                            <div class="form-group">
                                <label>URL</label>
                                <input type="text" name="item_url[]" class="form-input" value="<?= htmlspecialchars($item['url']) ?>">
                            </div>
                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="item_target[]" value="_blank" <?= ($item['target'] ?? '') === '_blank' ? 'checked' : '' ?>>
                                    Open in new tab
                                </label>
                            </div>
                            <button type="button" class="btn btn-text text-danger btn-sm" onclick="this.closest('.menu-item-card').remove()">Remove</button>
                        </div>
                    </div>
                    <?php endforeach; endif; ?>
                </div>

                <div style="display:flex;justify-content:space-between;align-items:center;padding-top:16px;border-top:1px solid var(--glass-border);margin-top:16px">
                    <a href="<?= $adminUrl ?>/menus/delete/<?= $selectedMenu['id'] ?>" class="btn btn-text text-danger confirm-delete" data-confirm="Delete this menu?">Delete Menu</a>
                    <button type="submit" class="btn btn-primary">Save Menu</button>
                </div>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<style>
.menu-layout { display: grid; grid-template-columns: 300px 1fr; gap: 24px; align-items: start; }
.menu-sidebar { display: flex; flex-direction: column; gap: 16px; }
.taxonomy-checklist { display: flex; flex-direction: column; gap: 6px; }
.checkbox-label { display: flex; align-items: center; gap: 8px; font-size: 14px; cursor: pointer; }
.menu-items-list { display: flex; flex-direction: column; gap: 8px; }
.menu-item-card {
    border: 1px solid var(--glass-border);
    border-radius: 8px;
    background: var(--glass-bg);
    overflow: hidden;
}
.menu-item-header {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 12px;
    cursor: grab;
}
.menu-item-drag { opacity: .4; cursor: grab; }
.menu-item-label { flex: 1; font-weight: 500; }
.menu-item-toggle { background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 12px; }
.menu-item-body {
    display: none;
    padding: 12px;
    border-top: 1px solid var(--glass-border);
}
.menu-item-card.open .menu-item-body { display: block; }
.menu-item-card.open .menu-item-toggle { transform: rotate(180deg); }
@media (max-width: 900px) { .menu-layout { grid-template-columns: 1fr; } }
</style>
