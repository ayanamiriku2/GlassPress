<?php \GlassPress\Core\View::startSection('content'); ?>

<div class="content-wrap <?= ($settings->get('show_sidebar', '1') === '1') ? 'has-sidebar' : '' ?>">
    <div class="primary-content">
        <?php if (!empty($pageTitle)): ?>
        <h1 class="archive-title"><?= htmlspecialchars($pageTitle) ?></h1>
        <?php endif; ?>

        <?php if (empty($posts)): ?>
        <div class="glass-card" style="text-align:center;padding:60px 24px">
            <h2>No posts yet</h2>
            <p class="text-muted">Check back soon for new content.</p>
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
                <div class="post-meta">
                    <?php if (!empty($post['categories'])): ?>
                    <?php foreach ($post['categories'] as $cat): ?>
                    <a href="<?= $siteUrl ?>/<?= $settings->get('category_base', 'category') ?>/<?= $cat['slug'] ?>" class="post-category"><?= htmlspecialchars($cat['name']) ?></a>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <h2 class="post-card-title">
                    <a href="<?= $siteUrl ?>/<?= $post['slug'] ?>"><?= htmlspecialchars($post['title']) ?></a>
                </h2>

                <p class="post-excerpt">
                    <?php if ($post['excerpt']): ?>
                        <?= htmlspecialchars($post['excerpt']) ?>
                    <?php else: ?>
                        <?= htmlspecialchars(mb_substr(strip_tags($post['content']), 0, 200)) ?>...
                    <?php endif; ?>
                </p>

                <div class="post-card-footer">
                    <div class="post-author">
                        <?php if (!empty($post['author_name'])): ?>
                        <span class="author-name"><?= htmlspecialchars($post['author_name']) ?></span>
                        <?php endif; ?>
                    </div>
                    <time datetime="<?= $post['published_at'] ?? $post['created_at'] ?>">
                        <?= date($dateFormat, strtotime($post['published_at'] ?? $post['created_at'])) ?>
                    </time>
                    <?php if ($settings->get('show_reading_time', '0') === '1'): ?>
                    <span class="reading-time"><?= ceil(str_word_count(strip_tags($post['content'])) / 200) ?> min read</span>
                    <?php endif; ?>
                </div>
            </div>
        </article>
        <?php endforeach; ?>

        <?php if ($totalPages > 1): ?>
        <nav class="pagination">
            <?php if ($page > 1): ?>
            <a href="<?= $page === 2 ? $siteUrl . '/' : $siteUrl . '/page/' . ($page - 1) ?>" class="btn btn-outline">&larr; Newer Posts</a>
            <?php endif; ?>
            <span class="page-info">Page <?= $page ?> of <?= $totalPages ?></span>
            <?php if ($page < $totalPages): ?>
            <a href="<?= $siteUrl ?>/page/<?= $page + 1 ?>" class="btn btn-outline">Older Posts &rarr;</a>
            <?php endif; ?>
        </nav>
        <?php endif; ?>
    </div>

    <?php if ($settings->get('show_sidebar', '1') === '1'): ?>
    <?php \GlassPress\Core\View::partial('theme.partials.sidebar', get_defined_vars()); ?>
    <?php endif; ?>
</div>

<?php \GlassPress\Core\View::endSection(); ?>
