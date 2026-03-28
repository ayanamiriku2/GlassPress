<?php \GlassPress\Core\View::startSection('content'); ?>

<article class="single-page full-width">
    <?php if (!empty($page['featured_image'])): ?>
    <div class="post-hero full-width-hero">
        <img src="<?= $siteUrl ?>/<?= htmlspecialchars($page['featured_image']) ?>" alt="<?= htmlspecialchars($page['title']) ?>" class="hero-image">
    </div>
    <?php endif; ?>

    <div class="full-width-content">
        <h1 class="page-title"><?= htmlspecialchars($page['title']) ?></h1>
        <div class="post-content prose">
            <?= $page['content'] ?>
        </div>
    </div>
</article>

<?php \GlassPress\Core\View::endSection(); ?>
