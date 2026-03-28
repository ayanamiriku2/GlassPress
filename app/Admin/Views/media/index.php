<div class="content-header">
    <div class="content-header-left">
        <h1>Media Library</h1>
        <a href="<?= $adminUrl ?>/media/create" class="btn btn-primary">+ Upload New</a>
    </div>
    <div class="content-header-right">
        <form class="search-bar" method="get" action="<?= $adminUrl ?>/media">
            <input type="text" name="s" placeholder="Search media…" value="<?= htmlspecialchars($search ?? '') ?>">
            <button type="submit">Search</button>
        </form>
    </div>
</div>

<div class="filter-tabs">
    <?php
    $types = ['' => 'All', 'image' => 'Images', 'document' => 'Documents', 'video' => 'Videos', 'audio' => 'Audio'];
    foreach ($types as $key => $label):
    ?>
    <a href="<?= $adminUrl ?>/media<?= $key ? "?type={$key}" : '' ?>" 
       class="filter-tab <?= ($type ?? '') === $key ? 'active' : '' ?>"><?= $label ?></a>
    <?php endforeach; ?>
</div>

<div class="glass-card">
    <?php if (empty($media)): ?>
    <div class="empty-state">
        <div class="empty-state-content">
            <span style="font-size:48px">🖼️</span>
            <h3>No media found</h3>
            <p>Upload your first file to get started.</p>
            <a href="<?= $adminUrl ?>/media/create" class="btn btn-primary">Upload Media</a>
        </div>
    </div>
    <?php else: ?>
    <div class="media-grid">
        <?php foreach ($media as $item): 
            $isImage = str_starts_with($item['mime_type'], 'image/');
            $thumbUrl = $item['file_path'];
            if ($isImage && !empty($item['sizes'])) {
                $sizes = json_decode($item['sizes'], true);
                if (!empty($sizes['thumbnail']['url'])) {
                    $thumbUrl = $sizes['thumbnail']['url'];
                }
            }
        ?>
        <a href="<?= $adminUrl ?>/media/edit/<?= $item['id'] ?>" class="media-grid-item">
            <?php if ($isImage): ?>
                <img src="<?= $thumbUrl ?>" 
                     alt="<?= htmlspecialchars($item['alt_text'] ?? $item['filename']) ?>"
                     loading="lazy">
            <?php else: ?>
                <div class="media-file-icon">
                    <?php
                    $icon = '📄';
                    if (str_starts_with($item['mime_type'], 'video/')) $icon = '🎬';
                    elseif (str_starts_with($item['mime_type'], 'audio/')) $icon = '🎵';
                    elseif ($item['mime_type'] === 'application/pdf') $icon = '📑';
                    echo $icon;
                    ?>
                </div>
            <?php endif; ?>
            <div class="media-grid-info">
                <span class="media-filename"><?= htmlspecialchars($item['title'] ?: $item['filename']) ?></span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <?php if ($pages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
            <?php $qs = array_filter(['type' => $type, 's' => $search, 'paged' => ($i > 1 ? $i : null)]); ?>
            <a href="<?= $adminUrl ?>/media<?= $qs ? '?' . http_build_query($qs) : '' ?>" 
               class="page-link <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<style>
.media-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 16px;
    padding: 16px;
}
.media-grid-item {
    aspect-ratio: 1;
    border-radius: 8px;
    overflow: hidden;
    border: 2px solid transparent;
    transition: all .2s;
    display: flex;
    flex-direction: column;
    background: var(--glass-bg);
    text-decoration: none;
    color: inherit;
}
.media-grid-item:hover {
    border-color: var(--accent);
    transform: translateY(-2px);
}
.media-grid-item img {
    width: 100%;
    height: 130px;
    object-fit: cover;
}
.media-file-icon {
    height: 130px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
}
.media-grid-info {
    padding: 8px;
    font-size: 12px;
    overflow: hidden;
}
.media-filename {
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
