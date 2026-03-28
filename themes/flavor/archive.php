<?php \GlassPress\Core\View::startSection('content'); ?>

<div class="content-wrap <?= ($settings->get('show_sidebar', '1') === '1') ? 'has-sidebar' : '' ?>">
    <div class="primary-content">
        <header class="archive-header glass-card">
            <?php if ($archiveType === 'category'): ?>
            <span class="archive-type">Category</span>
            <h1 class="archive-title"><?= htmlspecialchars($taxonomy['name']) ?></h1>
            <?php if (!empty($taxonomy['description'])): ?>
            <p class="archive-desc"><?= htmlspecialchars($taxonomy['description']) ?></p>
            <?php endif; ?>
            <?php elseif ($archiveType === 'tag'): ?>
            <span class="archive-type">Tag</span>
            <h1 class="archive-title">#<?= htmlspecialchars($taxonomy['name']) ?></h1>
            <?php elseif ($archiveType === 'author'): ?>
            <span class="archive-type">Author</span>
            <h1 class="archive-title"><?= htmlspecialchars($author['display_name']) ?></h1>
            <?php if (!empty($author['bio'])): ?>
            <p class="archive-desc"><?= htmlspecialchars($author['bio']) ?></p>
            <?php endif; ?>
            <?php elseif ($archiveType === 'date'): ?>
            <span class="archive-type">Archives</span>
            <h1 class="archive-title"><?= htmlspecialchars($pageTitle) ?></h1>
            <?php endif; ?>
            <p class="archive-count"><?= $total ?> post<?= $total !== 1 ? 's' : '' ?></p>
        </header>

        <?php if (empty($posts)): ?>
        <div class="glass-card" style="text-align:center;padding:40px">
            <p class="text-muted">No posts found.</p>
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
                <h2 class="post-card-title">
                    <a href="<?= $siteUrl ?>/<?= $post['slug'] ?>"><?= htmlspecialchars($post['title']) ?></a>
                </h2>
                <p class="post-excerpt">
                    <?= htmlspecialchars($post['excerpt'] ?? mb_substr(strip_tags($post['content'] ?? ''), 0, 200)) ?>
                </p>
                <div class="post-card-footer">
                    <time datetime="<?= $post['published_at'] ?? $post['created_at'] ?>">
                        <?= date($dateFormat, strtotime($post['published_at'] ?? $post['created_at'])) ?>
                    </time>
                </div>
            </div>
        </article>
        <?php endforeach; ?>

        <?php if ($totalPages > 1): ?>
        <nav class="pagination">
            <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>" class="btn btn-outline">&larr; Previous</a>
            <?php endif; ?>
            <span class="page-info">Page <?= $page ?> of <?= $totalPages ?></span>
            <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>" class="btn btn-outline">Next &rarr;</a>
            <?php endif; ?>
        </nav>
        <?php endif; ?>
    </div>

    <?php if ($settings->get('show_sidebar', '1') === '1'): ?>
    <?php \GlassPress\Core\View::partial('theme.partials.sidebar', get_defined_vars()); ?>
    <?php endif; ?>
</div>

<?php \GlassPress\Core\View::endSection(); ?>
