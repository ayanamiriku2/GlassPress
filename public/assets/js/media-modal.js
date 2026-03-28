/* GlassPress Media Library Modal */
(function() {
    'use strict';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    let currentPage = 1;
    let hasMore = true;
    let selectedMedia = null;
    let mediaContext = null; // 'featured-image' or 'editor-image'
    let onSelectCallback = null;

    const gpMedia = {
        switchTab(tab) {
            document.querySelectorAll('.media-tab').forEach(t => t.classList.remove('active'));
            document.querySelector(`.media-tab[data-tab="${tab}"]`).classList.add('active');
            document.getElementById('media-tab-browse').style.display = tab === 'browse' ? '' : 'none';
            document.getElementById('media-tab-upload').style.display = tab === 'upload' ? '' : 'none';
        },

        open(context, callback) {
            mediaContext = context;
            onSelectCallback = callback || null;
            selectedMedia = null;
            currentPage = 1;
            hasMore = true;

            document.getElementById('media-grid-container').innerHTML = '';
            document.getElementById('media-selected-info').textContent = '';
            document.getElementById('media-insert-btn').disabled = true;
            document.getElementById('media-upload-progress').innerHTML = '';
            this.switchTab('browse');

            openModal('media-modal');
            this.loadImages();
        },

        loadImages() {
            const grid = document.getElementById('media-grid-container');
            const empty = document.getElementById('media-empty');
            const more = document.getElementById('media-load-more');

            fetch('/api/media/list?page=' + currentPage, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;

                const items = data.media || [];
                if (items.length < 20) {
                    hasMore = false;
                    more.style.display = 'none';
                } else {
                    more.style.display = '';
                }

                if (items.length === 0 && currentPage === 1) {
                    empty.style.display = '';
                    return;
                }
                empty.style.display = 'none';

                items.forEach(item => {
                    const el = document.createElement('div');
                    el.className = 'media-picker-item';
                    el.dataset.id = item.id;
                    el.dataset.url = item.file_path;
                    el.dataset.alt = item.alt_text || '';
                    el.dataset.name = item.original_name || item.filename;
                    el.innerHTML = `<img src="${item.file_path}" alt="${this.esc(item.alt_text || '')}" loading="lazy"><div class="media-picker-check">✓</div>`;
                    el.addEventListener('click', () => this.selectItem(el, item));
                    grid.appendChild(el);
                });
            })
            .catch(() => {});
        },

        loadMore() {
            if (!hasMore) return;
            currentPage++;
            this.loadImages();
        },

        selectItem(el, item) {
            document.querySelectorAll('.media-picker-item.selected').forEach(s => s.classList.remove('selected'));
            el.classList.add('selected');
            selectedMedia = item;
            document.getElementById('media-insert-btn').disabled = false;
            const info = document.getElementById('media-selected-info');
            const name = item.original_name || item.filename;
            const size = item.width && item.height ? ` — ${item.width}×${item.height}` : '';
            info.textContent = name + size;
        },

        insertSelected() {
            if (!selectedMedia) return;

            if (onSelectCallback) {
                onSelectCallback(selectedMedia);
            } else if (mediaContext === 'featured-image') {
                this.setFeaturedImage(selectedMedia);
            } else if (mediaContext === 'editor-image') {
                this.insertEditorImage(selectedMedia);
            }

            closeModal('media-modal');
        },

        setFeaturedImage(media) {
            const idInput = document.getElementById('featured-image-id');
            const preview = document.getElementById('featured-image-preview');
            const removeBtn = document.getElementById('remove-featured-image');

            if (idInput) idInput.value = media.id;
            if (preview) {
                preview.innerHTML = `<img src="${media.file_path}" alt="${this.esc(media.alt_text || '')}">`;
            }
            if (removeBtn) removeBtn.style.display = '';
        },

        insertEditorImage(media) {
            const visual = document.getElementById('editor-visual');
            const html = document.getElementById('editor-html');
            if (!visual) return;

            const isHtmlMode = visual.style.display === 'none';
            const alt = this.esc(media.alt_text || media.original_name || '');
            const imgTag = `<img src="${media.file_path}" alt="${alt}" style="max-width:100%;height:auto">`;

            if (isHtmlMode && html) {
                // Insert at cursor in textarea
                const start = html.selectionStart;
                const end = html.selectionEnd;
                html.value = html.value.substring(0, start) + imgTag + html.value.substring(end);
                html.selectionStart = html.selectionEnd = start + imgTag.length;
                html.focus();
            } else {
                // Insert in contenteditable
                visual.focus();
                document.execCommand('insertHTML', false, imgTag);
            }
        },

        handleUpload(files) {
            const progress = document.getElementById('media-upload-progress');
            progress.innerHTML = '';

            Array.from(files).forEach(file => {
                if (!file.type.startsWith('image/')) {
                    progress.innerHTML += `<div class="upload-item upload-error">${this.esc(file.name)}: Not an image</div>`;
                    return;
                }

                const item = document.createElement('div');
                item.className = 'upload-item';
                item.innerHTML = `<span>${this.esc(file.name)}</span><span class="upload-status">Uploading…</span>`;
                progress.appendChild(item);

                const formData = new FormData();
                formData.append('file', file);
                formData.append('csrf_token', csrfToken);

                fetch('/api/media/upload', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        item.querySelector('.upload-status').textContent = '✓ Uploaded';
                        item.classList.add('upload-success');

                        // Add to browse grid
                        const grid = document.getElementById('media-grid-container');
                        const el = document.createElement('div');
                        el.className = 'media-picker-item';
                        el.dataset.id = data.media.id;
                        el.dataset.url = data.media.url;
                        el.dataset.alt = '';
                        el.dataset.name = data.media.filename;
                        el.innerHTML = `<img src="${data.media.url}" alt="" loading="lazy"><div class="media-picker-check">✓</div>`;

                        const mediaItem = {
                            id: data.media.id,
                            file_path: data.media.url,
                            filename: data.media.filename,
                            alt_text: '',
                            original_name: file.name,
                            width: data.media.width,
                            height: data.media.height
                        };
                        el.addEventListener('click', () => gpMedia.selectItem(el, mediaItem));
                        grid.insertBefore(el, grid.firstChild);

                        // Auto-select the just-uploaded image
                        gpMedia.selectItem(el, mediaItem);
                    } else {
                        item.querySelector('.upload-status').textContent = '✗ ' + (data.error || 'Failed');
                        item.classList.add('upload-error');
                    }
                })
                .catch(() => {
                    item.querySelector('.upload-status').textContent = '✗ Error';
                    item.classList.add('upload-error');
                });
            });
        },

        esc(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
    };

    // Upload zone events
    const zone = document.getElementById('media-upload-zone');
    const input = document.getElementById('media-upload-input');

    if (zone && input) {
        zone.addEventListener('click', () => input.click());

        zone.addEventListener('dragover', e => {
            e.preventDefault();
            zone.classList.add('dragover');
        });

        zone.addEventListener('dragleave', () => {
            zone.classList.remove('dragover');
        });

        zone.addEventListener('drop', e => {
            e.preventDefault();
            zone.classList.remove('dragover');
            if (e.dataTransfer.files.length) {
                gpMedia.handleUpload(e.dataTransfer.files);
            }
        });

        input.addEventListener('change', () => {
            if (input.files.length) {
                gpMedia.handleUpload(input.files);
                input.value = '';
            }
        });
    }

    // Expose globally
    window.gpMedia = gpMedia;

    // Replace placeholder openMediaModal
    window.openMediaModal = function(context, callback) {
        gpMedia.open(context, callback);
    };
})();
