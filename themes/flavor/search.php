<?php \GlassPress\Core\View::startSection('content'); ?>

<div class="content-wrap <?= ($settings->get('show_sidebar', '1') === '1') ? 'has-sidebar' : '' ?>">
    <div class="primary-content">
        <header class="archive-header glass-card">
            <h1 class="archive-title">Search<?= $query ? ': ' . $query : '' ?></h1>
            <?php if ($total > 0): ?>
            <p class="archive-count"><?= $total ?> result<?= $total !== 1 ? 's' : '' ?> found</p>
            <?php endif; ?>
        </header>

        <form action="<?= $siteUrl ?>/search" method="get" class="search-form-large glass-card">
            <input type="text" name="q" class="form-input search-large" value="<?= $query ?>" placeholder="Search posts, pages..." autofocus>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>

        <?php if (empty($posts) && $query): ?>
        <div class="glass-card" style="text-align:center;padding:40px">
            <h3>No results found</h3>
            <p class="text-muted">Try adjusting your search terms or browse the categories below.</p>
        </div>
        <?php endif; ?>

        <?php foreach ($posts as $post): ?>
        <article class="post-card glass-card">
            <?php if (!empty($post['featured_image'])): ?>
            <a href="<?= $siteUrl ?>/<?= $post['slug'] ?>" class="post-card-image">
                <img src="<?= $siteUrl ?>/<?= htmlspecialchars($post['featured_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" loading="lazy">
            </a>
            <?php endif; ?>

            <div class="post-card-body">
                <span class="post-type-badge"><?= ucfirst($post['post_type'] ?? 'post') ?></span>
                <h2 class="post-card-title">
                    <a href="<?= $siteUrl ?>/<?= $post['slug'] ?>"><?= htmlspecialchars($post['title']) ?></a>
                </h2>
                <p class="post-excerpt">
                    <?= htmlspecialchars(mb_substr(strip_tags($post['content'] ?? ''), 0, 200)) ?>...
                </p>
                <div class="post-card-footer">
                    <time><?= date($dateFormat, strtotime($post['published_at'] ?? $post['created_at'])) ?></time>
                </div>
            </div>
        </article>
        <?php endforeach; ?>

        <?php if ($totalPages > 1): ?>
        <nav class="pagination">
            <?php if ($page > 1): ?>
            <a href="?q=<?= urlencode($query) ?>&page=<?= $page - 1 ?>" class="btn btn-outline">&larr; Previous</a>
            <?php endif; ?>
            <span class="page-info">Page <?= $page ?> of <?= $totalPages ?></span>
            <?php if ($page < $totalPages): ?>
            <a href="?q=<?= urlencode($query) ?>&page=<?= $page + 1 ?>" class="btn btn-outline">Next &rarr;</a>
            <?php endif; ?>
        </nav>
        <?php endif; ?>
    </div>

    <?php if ($settings->get('show_sidebar', '1') === '1'): ?>
    <?php \GlassPress\Core\View::partial('theme.partials.sidebar', get_defined_vars()); ?>
    <?php endif; ?>
</div>

<?php \GlassPress\Core\View::endSection(); ?>
