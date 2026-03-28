<?php
$isImage = str_starts_with($item['mime_type'], 'image/');
?>

<div class="content-header">
    <div class="content-header-left">
        <h1>Edit Media</h1>
        <a href="<?= $adminUrl ?>/media" class="btn btn-default">← Back to Library</a>
    </div>
</div>

<form method="post" action="<?= $adminUrl ?>/media/update/<?= $item['id'] ?>">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

    <div class="editor-layout">
        <div class="editor-main">
            <div class="glass-card">
                <?php if ($isImage): ?>
                <div style="text-align:center;padding:20px">
                    <img src="<?= $siteUrl . $item['file_path'] ?>" 
                         alt="<?= htmlspecialchars($item['alt_text'] ?? '') ?>"
                         style="max-width:100%;max-height:500px;border-radius:8px">
                </div>
                <?php else: ?>
                <div style="text-align:center;padding:40px;font-size:72px">📄</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="editor-sidebar">
            <div class="glass-card">
                <h3 class="card-title">Details</h3>
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" class="form-input" value="<?= htmlspecialchars($item['original_name'] ?? '') ?>">
                </div>
                <?php if ($isImage): ?>
                <div class="form-group">
                    <label>Alt Text</label>
                    <input type="text" name="alt_text" class="form-input" value="<?= htmlspecialchars($item['alt_text'] ?? '') ?>">
                    <p class="form-hint">Describes the image for screen readers and SEO.</p>
                </div>
                <div class="form-group">
                    <label>Caption</label>
                    <textarea name="caption" rows="2" class="form-input"><?= htmlspecialchars($item['caption'] ?? '') ?></textarea>
                </div>
                <?php endif; ?>
                <div class="publish-actions">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </div>

            <div class="glass-card">
                <h3 class="card-title">File Info</h3>
                <div class="form-meta">
                    <span>Filename: <?= htmlspecialchars($item['filename']) ?></span>
                    <span>Type: <?= htmlspecialchars($item['mime_type']) ?></span>
                    <span>Size: <?= number_format($item['file_size'] / 1024, 1) ?> KB</span>
                    <?php if ($item['width']): ?>
                    <span>Dimensions: <?= $item['width'] ?> × <?= $item['height'] ?></span>
                    <?php endif; ?>
                    <span>Uploaded: <?= date('M j, Y g:i A', strtotime($item['created_at'])) ?></span>
                    <span>By: <?= htmlspecialchars($item['uploader_name'] ?? 'Unknown') ?></span>
                </div>
            </div>

            <div class="glass-card">
                <h3 class="card-title">URL</h3>
                <input type="text" class="form-input" value="<?= $siteUrl . $item['file_path'] ?>" readonly onclick="this.select()">
                <p class="form-hint" style="margin-top:8px">Click to select. Copy this URL to use in content.</p>
            </div>

            <div class="glass-card">
                <form method="post" action="<?= $adminUrl ?>/media/delete/<?= $item['id'] ?>" 
                      onsubmit="return confirm('Permanently delete this file? This cannot be undone.')">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Delete Permanently</button>
                </form>
            </div>
        </div>
    </div>
</form>

<style>
.editor-layout { display: grid; grid-template-columns: 1fr 320px; gap: 24px; align-items: start; }
.editor-main { min-width: 0; }
.editor-sidebar { display: flex; flex-direction: column; gap: 16px; }
.form-meta { display: flex; flex-direction: column; gap: 4px; font-size: 12px; color: var(--text-muted); }
.publish-actions { display: flex; gap: 8px; justify-content: flex-end; padding-top: 12px; border-top: 1px solid var(--glass-border); }
@media (max-width: 1024px) { .editor-layout { grid-template-columns: 1fr; } }
</style>
