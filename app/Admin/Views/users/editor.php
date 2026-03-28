<?php
$u = $editUser ?? null;
$isEdit = !($isNew ?? true);
?>

<div class="content-header">
    <div class="content-header-left">
        <h1><?= $isEdit ? 'Edit User' : 'Add New User' ?></h1>
        <a href="<?= $adminUrl ?>/users" class="btn btn-default">← Back</a>
    </div>
</div>

<div class="glass-card" style="max-width:700px">
    <form method="post" action="<?= $adminUrl ?>/users<?= $isEdit ? "/update/{$u['id']}" : '/store' ?>">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

        <?php if (!$isEdit): ?>
        <div class="form-group">
            <label>Username <span class="required">*</span></label>
            <input type="text" name="username" class="form-input" required autocomplete="off" pattern="[a-zA-Z0-9_]+" title="Letters, numbers, and underscores only">
        </div>
        <?php else: ?>
        <div class="form-group">
            <label>Username</label>
            <input type="text" class="form-input" value="<?= htmlspecialchars($u['username']) ?>" disabled>
            <p class="form-hint">Usernames cannot be changed.</p>
        </div>
        <?php endif; ?>

        <div class="form-group">
            <label>Email <span class="required">*</span></label>
            <input type="email" name="email" class="form-input" required value="<?= htmlspecialchars($u['email'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Display Name</label>
            <input type="text" name="display_name" class="form-input" value="<?= htmlspecialchars($u['display_name'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label><?= $isEdit ? 'New Password (leave blank to keep current)' : 'Password' ?> <?= !$isEdit ? '' : '' ?></label>
            <input type="password" name="<?= $isEdit ? 'new_password' : 'password' ?>" class="form-input" 
                   <?= !$isEdit ? 'required' : '' ?> minlength="8" autocomplete="new-password">
            <p class="form-hint">Minimum 8 characters.</p>
        </div>

        <?php if ($user['role'] === 'administrator'): ?>
        <div class="form-group">
            <label>Role</label>
            <select name="role" class="form-input">
                <?php
                $roles = ['administrator' => 'Administrator', 'editor' => 'Editor', 'author' => 'Author', 'contributor' => 'Contributor', 'subscriber' => 'Subscriber'];
                foreach ($roles as $rKey => $rLabel):
                ?>
                <option value="<?= $rKey ?>" <?= ($u['role'] ?? 'subscriber') === $rKey ? 'selected' : '' ?>><?= $rLabel ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if ($isEdit): ?>
        <div class="form-group">
            <label>Status</label>
            <select name="status" class="form-input">
                <option value="active" <?= ($u['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="suspended" <?= ($u['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
            </select>
        </div>
        <?php endif; endif; ?>

        <div class="form-group">
            <label>Bio</label>
            <textarea name="bio" rows="4" class="form-input"><?= htmlspecialchars($u['bio'] ?? '') ?></textarea>
        </div>

        <?php if ($isEdit): ?>
        <div class="form-meta" style="margin-bottom:12px">
            <span>Member since: <?= date('M j, Y', strtotime($u['created_at'])) ?></span>
            <?php if ($u['last_login']): ?>
            <span>Last login: <?= date('M j, Y g:i A', strtotime($u['last_login'])) ?></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="publish-actions">
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update User' : 'Add User' ?></button>
        </div>
    </form>
</div>

<style>
.required { color: var(--danger); }
.form-meta { display: flex; flex-direction: column; gap: 4px; font-size: 12px; color: var(--text-muted); }
.publish-actions { display: flex; gap: 8px; justify-content: flex-end; padding-top: 12px; border-top: 1px solid var(--glass-border); }
</style>
