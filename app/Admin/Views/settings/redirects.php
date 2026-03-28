<div class="content-header"><div class="content-header-left"><h1>Redirects</h1></div></div>

<?php $tabs = ['general'=>'General','writing'=>'Writing','reading'=>'Reading','discussion'=>'Discussion','media'=>'Media','permalinks'=>'Permalinks','seo'=>'SEO','redirects'=>'Redirects','appearance'=>'Appearance','advanced'=>'Advanced']; $settingsPage = 'redirects'; ?>
<div class="settings-tabs"><?php foreach ($tabs as $key => $label): ?><a href="<?= $adminUrl ?>/settings/<?= $key ?>" class="settings-tab <?= $settingsPage === $key ? 'active' : '' ?>"><?= $label ?></a><?php endforeach; ?></div>

<div class="glass-card" style="max-width:800px">
    <h3 class="card-title">Add New Redirect</h3>
    <form method="post" action="<?= $adminUrl ?>/settings/redirects/store" style="display:flex;gap:12px;align-items:end;flex-wrap:wrap">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
        <div class="form-group" style="flex:1;min-width:200px">
            <label>Source URL</label>
            <input type="text" name="source_url" class="form-input" placeholder="/old-page" required>
        </div>
        <div class="form-group" style="flex:1;min-width:200px">
            <label>Target URL</label>
            <input type="text" name="target_url" class="form-input" placeholder="/new-page" required>
        </div>
        <div class="form-group" style="width:100px">
            <label>Type</label>
            <select name="status_code" class="form-input">
                <option value="301">301</option>
                <option value="302">302</option>
                <option value="307">307</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary" style="margin-bottom:16px">Add</button>
    </form>
</div>

<div class="glass-card" style="max-width:800px;margin-top:24px">
    <h3 class="card-title">Active Redirects</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Source</th>
                <th>Target</th>
                <th>Type</th>
                <th>Hits</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($redirects)): ?>
            <tr><td colspan="6" style="text-align:center;padding:24px;color:var(--text-muted)">No redirects configured.</td></tr>
            <?php else: foreach ($redirects as $r): ?>
            <tr>
                <td><code><?= htmlspecialchars($r['source_url']) ?></code></td>
                <td><code><?= htmlspecialchars($r['target_url']) ?></code></td>
                <td><?= $r['status_code'] ?></td>
                <td><?= (int) ($r['hit_count'] ?? 0) ?></td>
                <td><?= date('M j, Y', strtotime($r['created_at'])) ?></td>
                <td>
                    <a href="<?= $adminUrl ?>/settings/redirects/delete/<?= $r['id'] ?>" class="text-danger confirm-delete">Delete</a>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<style>
.settings-tabs{display:flex;gap:4px;margin-bottom:24px;flex-wrap:wrap}.settings-tab{padding:8px 16px;border-radius:8px 8px 0 0;color:var(--text-muted);text-decoration:none;font-size:14px;transition:all .2s;border:1px solid transparent;border-bottom:none}.settings-tab:hover{color:var(--text);background:var(--glass-bg)}.settings-tab.active{color:var(--text);background:var(--glass-bg);border-color:var(--glass-border);font-weight:600}
</style>
