<?php
$p = $post ?? null;
$isEdit = !($isNew ?? true);
$postType = $p ? $p['post_type'] : 'post';
$currentStatus = $p ? $p['status'] : 'draft';
$tagString = '';
if (!empty($postTags)) {
    $tagString = implode(', ', array_column($postTags, 'name'));
}
?>

<form method="post" action="<?= $adminUrl ?>/posts<?= $isEdit ? "/update/{$p['id']}" : '/store' ?>" id="post-editor-form" class="editor-layout" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    <input type="hidden" name="post_type" value="<?= $postType ?>">
    <?php if ($isEdit): ?>
    <input type="hidden" name="post_id" value="<?= $p['id'] ?>">
    <?php endif; ?>

    <div class="editor-main">
        <div class="glass-card" style="padding:0">
            <div class="editor-title-wrap">
                <input type="text" name="title" id="post-title" 
                       placeholder="Enter title here…" 
                       value="<?= htmlspecialchars($p['title'] ?? '') ?>"
                       class="editor-title-input" autocomplete="off">
            </div>
            <div class="editor-slug-wrap">
                <label>Permalink:</label>
                <span class="slug-base"><?= rtrim($siteUrl, '/') ?>/</span>
                <input type="text" name="slug" id="post-slug" 
                       value="<?= htmlspecialchars($p['slug'] ?? '') ?>"
                       class="editor-slug-input">
            </div>
        </div>

        <div class="glass-card">
            <div class="editor-toolbar">
                <button type="button" class="editor-btn" data-action="bold" title="Bold"><b>B</b></button>
                <button type="button" class="editor-btn" data-action="italic" title="Italic"><i>I</i></button>
                <button type="button" class="editor-btn" data-action="underline" title="Underline"><u>U</u></button>
                <span class="editor-separator"></span>
                <button type="button" class="editor-btn" data-action="heading" title="Heading">H</button>
                <button type="button" class="editor-btn" data-action="ul" title="Unordered List">• List</button>
                <button type="button" class="editor-btn" data-action="ol" title="Ordered List">1. List</button>
                <button type="button" class="editor-btn" data-action="blockquote" title="Blockquote">"</button>
                <span class="editor-separator"></span>
                <button type="button" class="editor-btn" data-action="link" title="Insert Link">🔗</button>
                <button type="button" class="editor-btn" data-action="image" title="Insert Image">🖼️</button>
                <button type="button" class="editor-btn" data-action="code" title="Code Block">&lt;/&gt;</button>
                <span class="editor-separator"></span>
                <button type="button" class="editor-btn" data-action="html" title="Toggle HTML" id="toggle-html">HTML</button>
            </div>
            <div id="editor-visual" class="editor-content" contenteditable="true"><?= $p['content'] ?? '' ?></div>
            <textarea name="content" id="editor-html" class="editor-content editor-textarea" style="display:none"><?= htmlspecialchars($p['content'] ?? '') ?></textarea>
        </div>

        <div class="glass-card">
            <h3 class="card-title">Excerpt</h3>
            <textarea name="excerpt" rows="3" class="form-input"><?= htmlspecialchars($p['excerpt'] ?? '') ?></textarea>
            <p class="form-hint">A short summary of the post. If left empty, an automatic excerpt will be generated from the content.</p>
        </div>

        <div class="glass-card" id="seo-panel">
            <h3 class="card-title" style="cursor:pointer" onclick="document.getElementById('seo-fields').classList.toggle('hidden')">
                SEO Settings <span style="float:right;opacity:.5">▼</span>
            </h3>
            <div id="seo-fields" class="<?= empty($seoMeta) ? 'hidden' : '' ?>">
                <div class="form-group">
                    <label>SEO Title</label>
                    <input type="text" name="seo_title" class="form-input" maxlength="70"
                           value="<?= htmlspecialchars($seoMeta['seo_title'] ?? '') ?>"
                           placeholder="Leave blank to use post title">
                    <div class="char-count" data-max="70"></div>
                </div>
                <div class="form-group">
                    <label>Meta Description</label>
                    <textarea name="seo_description" rows="2" class="form-input" maxlength="160"
                              placeholder="Leave blank to auto-generate from excerpt"><?= htmlspecialchars($seoMeta['seo_description'] ?? '') ?></textarea>
                    <div class="char-count" data-max="160"></div>
                </div>
                <div class="form-group">
                    <label>Focus Keyword</label>
                    <input type="text" name="focus_keyword" class="form-input"
                           value="<?= htmlspecialchars($seoMeta['focus_keyword'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Canonical URL</label>
                    <input type="url" name="canonical_url" class="form-input" 
                           value="<?= htmlspecialchars($seoMeta['canonical_url'] ?? '') ?>"
                           placeholder="Leave blank to use default URL">
                </div>
                <div class="form-group">
                    <label>Robots</label>
                    <select name="robots" class="form-input">
                        <option value="">Default (index, follow)</option>
                        <option value="noindex" <?= ($seoMeta['robots'] ?? '') === 'noindex' ? 'selected' : '' ?>>noindex</option>
                        <option value="nofollow" <?= ($seoMeta['robots'] ?? '') === 'nofollow' ? 'selected' : '' ?>>nofollow</option>
                        <option value="noindex, nofollow" <?= ($seoMeta['robots'] ?? '') === 'noindex, nofollow' ? 'selected' : '' ?>>noindex, nofollow</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>OG Title</label>
                    <input type="text" name="og_title" class="form-input"
                           value="<?= htmlspecialchars($seoMeta['og_title'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>OG Description</label>
                    <textarea name="og_description" rows="2" class="form-input"><?= htmlspecialchars($seoMeta['og_description'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>OG Image URL</label>
                    <input type="url" name="og_image" class="form-input"
                           value="<?= htmlspecialchars($seoMeta['og_image'] ?? '') ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="editor-sidebar">
        <!-- Publish Box -->
        <div class="glass-card">
            <h3 class="card-title">Publish</h3>
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-input" id="post-status">
                    <option value="draft" <?= $currentStatus === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="publish" <?= $currentStatus === 'publish' ? 'selected' : '' ?>>Published</option>
                    <option value="pending" <?= $currentStatus === 'pending' ? 'selected' : '' ?>>Pending Review</option>
                    <option value="scheduled" <?= $currentStatus === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                    <option value="private" <?= $currentStatus === 'private' ? 'selected' : '' ?>>Private</option>
                </select>
            </div>
            <div class="form-group" id="publish-date-group" style="<?= in_array($currentStatus, ['publish', 'scheduled']) ? '' : 'display:none' ?>">
                <label>Publish Date</label>
                <input type="datetime-local" name="published_at" class="form-input" 
                       value="<?= $p && $p['published_at'] ? date('Y-m-d\TH:i', strtotime($p['published_at'])) : '' ?>">
            </div>
            <?php if ($isEdit): ?>
            <div class="form-meta">
                <span>Created: <?= date('M j, Y g:i A', strtotime($p['created_at'])) ?></span>
                <?php if ($p['updated_at']): ?>
                <span>Updated: <?= date('M j, Y g:i A', strtotime($p['updated_at'])) ?></span>
                <?php endif; ?>
                <span>Word count: <span id="word-count">0</span></span>
            </div>
            <?php endif; ?>
            <div class="publish-actions">
                <button type="submit" name="status" value="draft" class="btn btn-default" style="<?= $currentStatus !== 'draft' ? 'display:none' : '' ?>">Save Draft</button>
                <button type="submit" class="btn btn-primary">
                    <?php if ($isEdit): ?>
                        Update
                    <?php else: ?>
                        Publish
                    <?php endif; ?>
                </button>
            </div>
        </div>

        <!-- Author -->
        <?php if ($user['role'] === 'administrator' || $user['role'] === 'editor'): ?>
        <div class="glass-card">
            <h3 class="card-title">Author</h3>
            <select name="author_id" class="form-input">
                <?php foreach ($authors as $author): ?>
                <option value="<?= $author['id'] ?>" <?= ($p['author_id'] ?? $user['id']) == $author['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($author['display_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <!-- Categories -->
        <div class="glass-card">
            <h3 class="card-title">Categories</h3>
            <div class="taxonomy-checklist" style="max-height:200px;overflow-y:auto">
                <?php foreach ($categories as $cat): ?>
                <label class="checkbox-label">
                    <input type="checkbox" name="categories[]" value="<?= $cat['id'] ?>" 
                           <?= in_array($cat['id'], $postCategories ?? []) ? 'checked' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </label>
                <?php endforeach; ?>
                <?php if (empty($categories)): ?>
                <p class="form-hint"><a href="<?= $adminUrl ?>/categories">Create categories</a></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tags -->
        <div class="glass-card">
            <h3 class="card-title">Tags</h3>
            <input type="text" name="tag_input" class="form-input" 
                   value="<?= htmlspecialchars($tagString) ?>"
                   placeholder="Separate tags with commas">
            <p class="form-hint">Enter tag names separated by commas. New tags will be created automatically.</p>
        </div>

        <!-- Featured Image -->
        <div class="glass-card">
            <h3 class="card-title">Featured Image</h3>
            <input type="hidden" name="featured_image_id" id="featured-image-id" value="<?= $p['featured_image_id'] ?? '' ?>">
            <div id="featured-image-preview" class="featured-img-preview">
                <?php
                if ($isEdit && !empty($p['featured_image_id'])):
                    $db = $GLOBALS['app']->getService('db');
                    $featImg = $db->fetch(
                        sprintf('SELECT file_path FROM %s WHERE id = ?', $db->prefix('media')),
                        [(int) $p['featured_image_id']]
                    );
                    if ($featImg):
                ?>
                    <img src="<?= htmlspecialchars($featImg['file_path']) ?>" alt="Featured image">
                <?php endif; endif; ?>
            </div>
            <div class="featured-img-actions">
                <button type="button" class="btn btn-default btn-sm" id="set-featured-image" 
                        onclick="openMediaModal('featured-image')">Set Featured Image</button>
                <button type="button" class="btn btn-text btn-sm" id="remove-featured-image" 
                        onclick="document.getElementById('featured-image-id').value='';document.getElementById('featured-image-preview').innerHTML='';this.style.display='none'"
                        style="<?= empty($p['featured_image_id']) ? 'display:none' : '' ?>">Remove</button>
            </div>
        </div>

        <!-- Discussion -->
        <div class="glass-card">
            <h3 class="card-title">Discussion</h3>
            <label class="checkbox-label">
                <input type="checkbox" name="comment_status" value="open" 
                       <?= ($p['comment_status'] ?? 'open') === 'open' ? 'checked' : '' ?>>
                Allow comments
            </label>
            <label class="checkbox-label" style="margin-top:8px">
                <input type="checkbox" name="is_sticky" value="1" 
                       <?= ($p['is_sticky'] ?? 0) ? 'checked' : '' ?>>
                Stick to top of blog
            </label>
        </div>
    </div>
</form>

<script>
(function() {
    const visual = document.getElementById('editor-visual');
    const html = document.getElementById('editor-html');
    const toggleBtn = document.getElementById('toggle-html');
    let isHtmlMode = false;

    // Toggle HTML/Visual
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            isHtmlMode = !isHtmlMode;
            if (isHtmlMode) {
                html.value = visual.innerHTML;
                visual.style.display = 'none';
                html.style.display = 'block';
                toggleBtn.classList.add('active');
            } else {
                visual.innerHTML = html.value;
                html.style.display = 'none';
                visual.style.display = 'block';
                toggleBtn.classList.remove('active');
            }
        });
    }

    // Sync content before submit
    document.getElementById('post-editor-form').addEventListener('submit', function() {
        if (!isHtmlMode) {
            html.value = visual.innerHTML;
        }
    });

    // Toolbar actions
    document.querySelectorAll('.editor-btn[data-action]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (isHtmlMode) return;
            const action = this.dataset.action;
            switch(action) {
                case 'bold': document.execCommand('bold'); break;
                case 'italic': document.execCommand('italic'); break;
                case 'underline': document.execCommand('underline'); break;
                case 'heading':
                    document.execCommand('formatBlock', false, '<h2>');
                    break;
                case 'ul': document.execCommand('insertUnorderedList'); break;
                case 'ol': document.execCommand('insertOrderedList'); break;
                case 'blockquote': document.execCommand('formatBlock', false, '<blockquote>'); break;
                case 'link':
                    var url = prompt('Enter URL:');
                    if (url) document.execCommand('createLink', false, url);
                    break;
                case 'image':
                    openMediaModal('editor-image');
                    break;
                case 'code':
                    document.execCommand('formatBlock', false, '<pre>');
                    break;
            }
            visual.focus();
        });
    });

    // Auto-generate slug from title
    const titleInput = document.getElementById('post-title');
    const slugInput = document.getElementById('post-slug');
    let slugEdited = slugInput.value !== '';

    slugInput.addEventListener('input', function() { slugEdited = true; });

    titleInput.addEventListener('blur', function() {
        if (!slugEdited && titleInput.value) {
            const slug = titleInput.value
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');
            slugInput.value = slug;
        }
    });

    // Show/hide publish date based on status
    const statusSelect = document.getElementById('post-status');
    const dateGroup = document.getElementById('publish-date-group');
    if (statusSelect && dateGroup) {
        statusSelect.addEventListener('change', function() {
            dateGroup.style.display = ['publish', 'scheduled'].includes(this.value) ? '' : 'none';
        });
    }

    // Word count
    function updateWordCount() {
        const text = (isHtmlMode ? html.value : visual.innerText).trim();
        const count = text ? text.split(/\s+/).length : 0;
        const el = document.getElementById('word-count');
        if (el) el.textContent = count;
    }

    if (visual) {
        visual.addEventListener('input', updateWordCount);
        updateWordCount();
    }

    // Autosave 
    <?php if ($isEdit): ?>
    let autosaveTimer;
    function setupAutosave() {
        let dirty = false;
        visual.addEventListener('input', () => dirty = true);
        titleInput.addEventListener('input', () => dirty = true);

        autosaveTimer = setInterval(function() {
            if (!dirty) return;
            dirty = false;

            if (!isHtmlMode) {
                html.value = visual.innerHTML;
            }

            const formData = new FormData();
            formData.append('csrf_token', '<?= $csrfToken ?>');
            formData.append('post_id', '<?= $p['id'] ?>');
            formData.append('title', titleInput.value);
            formData.append('content', html.value);
            formData.append('excerpt', document.querySelector('[name=excerpt]').value);

            fetch('<?= $siteUrl ?>/api/posts/autosave', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(d => {
                    if (d.success && typeof showNotification === 'function') {
                        showNotification('Autosaved at ' + d.time, 'info');
                    }
                })
                .catch(() => {});
        }, 30000);
    }
    setupAutosave();
    <?php endif; ?>
})();
</script>

<style>
.editor-layout {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 24px;
    align-items: start;
}
.editor-main { min-width: 0; }
.editor-sidebar { display: flex; flex-direction: column; gap: 16px; }
.editor-title-wrap { padding: 20px 20px 0; }
.editor-title-input {
    width: 100%;
    border: none;
    background: transparent;
    font-size: 24px;
    font-weight: 600;
    color: var(--text);
    outline: none;
    padding: 8px 0;
}
.editor-slug-wrap {
    padding: 8px 20px 16px;
    font-size: 13px;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 4px;
    border-bottom: 1px solid var(--glass-border);
}
.slug-base { opacity: .6; }
.editor-slug-input {
    border: 1px solid var(--glass-border);
    background: var(--glass-bg);
    color: var(--text);
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 13px;
    flex: 1;
}
.editor-toolbar {
    display: flex;
    gap: 2px;
    padding: 8px 12px;
    border-bottom: 1px solid var(--glass-border);
    flex-wrap: wrap;
}
.editor-btn {
    background: transparent;
    border: 1px solid transparent;
    color: var(--text);
    padding: 4px 10px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    transition: all .15s;
}
.editor-btn:hover { background: var(--glass-bg); border-color: var(--glass-border); }
.editor-btn.active { background: var(--accent); color: #fff; }
.editor-separator { width: 1px; background: var(--glass-border); margin: 0 6px; }
.editor-content {
    min-height: 400px;
    padding: 20px;
    font-size: 16px;
    line-height: 1.7;
    color: var(--text);
    outline: none;
}
.editor-content img { max-width: 100%; height: auto; }
.editor-content blockquote {
    border-left: 4px solid var(--accent);
    padding-left: 16px;
    margin: 16px 0;
    opacity: .8;
}
.editor-textarea {
    width: 100%;
    border: none;
    background: transparent;
    font-family: 'SFMono-Regular', Consolas, monospace;
    font-size: 14px;
    resize: vertical;
}
.form-meta {
    display: flex;
    flex-direction: column;
    gap: 4px;
    font-size: 12px;
    color: var(--text-muted);
    padding: 8px 0;
    border-top: 1px solid var(--glass-border);
    margin: 8px 0;
}
.publish-actions {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
    padding-top: 12px;
    border-top: 1px solid var(--glass-border);
}
.taxonomy-checklist { display: flex; flex-direction: column; gap: 6px; }
.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    cursor: pointer;
}
.char-count { font-size: 11px; color: var(--text-muted); text-align: right; margin-top: 4px; }
.hidden { display: none !important; }

/* Featured Image Preview */
.featured-img-preview {
    margin-bottom: 10px;
    border-radius: 8px;
    overflow: hidden;
    background: var(--glass-bg);
    border: 1px dashed var(--glass-border);
    aspect-ratio: 16/9;
    display: flex;
    align-items: center;
    justify-content: center;
}
.featured-img-preview:empty {
    display: none;
}
.featured-img-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.featured-img-actions {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
}

@media (max-width: 1024px) {
    .editor-layout { grid-template-columns: 1fr; }
    .editor-sidebar { order: 2; }
    .editor-main { order: 1; }
}
</style>
