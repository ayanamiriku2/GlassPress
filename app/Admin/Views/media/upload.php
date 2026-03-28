<div class="content-header">
    <div class="content-header-left">
        <h1>Upload Media</h1>
        <a href="<?= $adminUrl ?>/media" class="btn btn-default">← Back to Library</a>
    </div>
</div>

<div class="glass-card">
    <form method="post" action="<?= $adminUrl ?>/media/store" enctype="multipart/form-data" id="upload-form">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

        <div class="upload-dropzone" id="dropzone">
            <div class="dropzone-content">
                <span style="font-size:64px">📁</span>
                <h3>Drop files here or click to upload</h3>
                <p>Maximum upload file size: <?= ini_get('upload_max_filesize') ?></p>
                <input type="file" name="files[]" id="file-input" multiple 
                       accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.mp4,.mp3,.webm,.ogg,.webp">
                <button type="button" class="btn btn-primary" onclick="document.getElementById('file-input').click()">Select Files</button>
            </div>
        </div>

        <div id="file-preview" class="file-preview-list" style="display:none"></div>

        <div style="text-align:right;padding-top:16px" id="upload-btn-wrap" style="display:none">
            <button type="submit" class="btn btn-primary" id="upload-btn" style="display:none">Upload Files</button>
        </div>
    </form>
</div>

<style>
.upload-dropzone {
    border: 2px dashed var(--glass-border);
    border-radius: 12px;
    padding: 60px 20px;
    text-align: center;
    transition: all .3s;
    cursor: pointer;
}
.upload-dropzone.dragover {
    border-color: var(--accent);
    background: rgba(99, 102, 241, .1);
}
.dropzone-content h3 { margin: 16px 0 8px; }
.dropzone-content p { color: var(--text-muted); margin-bottom: 16px; }
.dropzone-content input[type=file] { display: none; }
.file-preview-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 12px;
    padding: 16px 0;
}
.file-preview-item {
    background: var(--glass-bg);
    border-radius: 8px;
    padding: 12px;
    text-align: center;
    font-size: 12px;
}
.file-preview-item img { width: 80px; height: 80px; object-fit: cover; border-radius: 4px; }
.file-preview-name { margin-top: 8px; word-break: break-all; }
</style>

<script>
(function() {
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('file-input');
    const preview = document.getElementById('file-preview');
    const uploadBtn = document.getElementById('upload-btn');

    dropzone.addEventListener('click', () => fileInput.click());
    dropzone.addEventListener('dragover', (e) => { e.preventDefault(); dropzone.classList.add('dragover'); });
    dropzone.addEventListener('dragleave', () => dropzone.classList.remove('dragover'));
    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.remove('dragover');
        fileInput.files = e.dataTransfer.files;
        showPreviews(e.dataTransfer.files);
    });

    fileInput.addEventListener('change', () => showPreviews(fileInput.files));

    function showPreviews(files) {
        preview.innerHTML = '';
        preview.style.display = 'grid';
        uploadBtn.style.display = 'inline-flex';

        Array.from(files).forEach(file => {
            const item = document.createElement('div');
            item.className = 'file-preview-item';

            if (file.type.startsWith('image/')) {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                item.appendChild(img);
            } else {
                const icon = document.createElement('div');
                icon.style.fontSize = '48px';
                icon.textContent = '📄';
                item.appendChild(icon);
            }

            const name = document.createElement('div');
            name.className = 'file-preview-name';
            name.textContent = file.name;
            item.appendChild(name);

            const size = document.createElement('div');
            size.style.color = 'var(--text-muted)';
            size.textContent = (file.size / 1024).toFixed(1) + ' KB';
            item.appendChild(size);

            preview.appendChild(item);
        });
    }
})();
</script>
