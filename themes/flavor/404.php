<?php \GlassPress\Core\View::startSection('content'); ?>

<div class="error-page">
    <div class="error-content glass-card">
        <h1 class="error-code">404</h1>
        <h2>Page Not Found</h2>
        <p class="text-muted">The page you're looking for doesn't exist or has been moved.</p>
        <div class="error-actions">
            <a href="<?= $siteUrl ?>" class="btn btn-primary">Go Home</a>
            <a href="<?= $siteUrl ?>/search" class="btn btn-outline">Search</a>
        </div>
    </div>
</div>

<?php \GlassPress\Core\View::endSection(); ?>
