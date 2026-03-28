/* GlassPress Admin JavaScript */
(function() {
    'use strict';

    // CSRF token for AJAX requests
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const basePath = window.GP_BASE_PATH || '';

    // Toggle sidebar children
    document.querySelectorAll('.nav-item.has-children > .nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            const item = this.closest('.nav-item');
            const children = item.querySelector('.nav-children');
            if (children) {
                e.preventDefault();
                children.classList.toggle('open');
                item.classList.toggle('active');
            }
        });
    });

    // Auto-dismiss toast
    const toast = document.getElementById('flash-toast');
    if (toast) {
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-10px)';
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }

    // Select all checkbox
    const selectAll = document.getElementById('select-all');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            document.querySelectorAll('.row-checkbox').forEach(cb => {
                cb.checked = this.checked;
            });
        });
    }

    // Confirm delete
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    });

    // Slug auto-generation from title
    const titleInput = document.getElementById('post-title');
    const slugInput = document.getElementById('post-slug');
    let slugManuallyEdited = false;

    if (titleInput && slugInput) {
        // If slug is empty, auto-generate
        if (!slugInput.value) {
            titleInput.addEventListener('input', function() {
                if (!slugManuallyEdited) {
                    slugInput.value = generateSlug(this.value);
                }
            });
        }
        
        slugInput.addEventListener('input', function() {
            slugManuallyEdited = true;
        });
    }

    function generateSlug(text) {
        return text.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/[\s-]+/g, '-')
            .replace(/^-|-$/g, '')
            .substring(0, 200);
    }

    // Autosave
    let autosaveTimer = null;
    const editorForm = document.getElementById('editor-form');
    
    if (editorForm) {
        const contentEl = document.getElementById('post-content') || document.querySelector('[name="content"]');
        let lastContent = contentEl ? contentEl.value : '';
        
        function scheduleAutosave() {
            clearTimeout(autosaveTimer);
            autosaveTimer = setTimeout(doAutosave, 30000); // 30 seconds
        }

        function doAutosave() {
            const formData = new FormData(editorForm);
            formData.append('_autosave', '1');

            fetch(basePath + '/api/posts/autosave', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showNotification('Draft auto-saved', 'info');
                }
            })
            .catch(() => {});
        }

        if (contentEl) {
            contentEl.addEventListener('input', scheduleAutosave);
        }
        if (titleInput) {
            titleInput.addEventListener('input', scheduleAutosave);
        }
    }

    // Show notification
    window.showNotification = function(message, type = 'success') {
        const existing = document.querySelector('.toast');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.className = 'toast toast-' + type;
        toast.innerHTML = '<span>' + message + '</span><button onclick="this.parentElement.remove()" class="toast-close">&times;</button>';
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-10px)';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    };

    // Modal system
    window.openModal = function(id) {
        document.getElementById(id)?.classList.add('active');
    };

    window.closeModal = function(id) {
        document.getElementById(id)?.classList.remove('active');
    };

    // Close modal on overlay click
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
            }
        });
    });

    // AJAX helper
    window.gpFetch = function(url, options = {}) {
        const defaults = {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
        };

        if (options.body && !(options.body instanceof FormData)) {
            defaults.headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(options.body);
        }

        return fetch(url, { ...defaults, ...options }).then(r => r.json());
    };

    // File upload drag/drop
    const dropZone = document.getElementById('media-dropzone');
    if (dropZone) {
        ['dragenter', 'dragover'].forEach(event => {
            dropZone.addEventListener(event, e => {
                e.preventDefault();
                dropZone.classList.add('drag-over');
            });
        });

        ['dragleave', 'drop'].forEach(event => {
            dropZone.addEventListener(event, e => {
                e.preventDefault();
                dropZone.classList.remove('drag-over');
            });
        });

        dropZone.addEventListener('drop', function(e) {
            const files = e.dataTransfer.files;
            if (files.length) {
                uploadFiles(files);
            }
        });
    }

    window.uploadFiles = function(files) {
        Array.from(files).forEach(file => {
            const formData = new FormData();
            formData.append('file', file);

            fetch(basePath + '/api/media/upload', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showNotification('File uploaded: ' + data.filename, 'success');
                    if (typeof onMediaUploaded === 'function') {
                        onMediaUploaded(data);
                    } else {
                        location.reload();
                    }
                } else {
                    showNotification('Upload failed: ' + (data.error || 'Unknown error'), 'error');
                }
            })
            .catch(() => {
                showNotification('Upload failed', 'error');
            });
        });
    };

    // Mobile sidebar toggle
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    if (sidebarToggle && window.innerWidth <= 768) {
        sidebarToggle.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-open');
        });
    }

    // Keyboard shortcut: Cmd/Ctrl+S = Save
    document.addEventListener('keydown', function(e) {
        if ((e.metaKey || e.ctrlKey) && e.key === 's') {
            e.preventDefault();
            const submitBtn = document.querySelector('[type="submit"].btn-primary');
            if (submitBtn) submitBtn.click();
        }
    });

})();
