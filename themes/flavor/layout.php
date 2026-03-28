<!DOCTYPE html>
<html lang="<?= $settings->get('language', 'en') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($seo['title'] ?? $siteName) ?></title>

    <?php if (!empty($seo['description'])): ?>
    <meta name="description" content="<?= htmlspecialchars($seo['description']) ?>">
    <?php endif; ?>

    <?php if (!empty($seo['robots'])): ?>
    <meta name="robots" content="<?= htmlspecialchars($seo['robots']) ?>">
    <?php endif; ?>

    <?php if (!empty($seo['canonical'])): ?>
    <link rel="canonical" href="<?= htmlspecialchars($seo['canonical']) ?>">
    <?php endif; ?>

    <!-- Open Graph -->
    <?php if (!empty($seo['og_title'])): ?>
    <meta property="og:title" content="<?= htmlspecialchars($seo['og_title']) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($seo['og_description'] ?? '') ?>">
    <meta property="og:type" content="<?= $seo['og_type'] ?? 'website' ?>">
    <meta property="og:url" content="<?= htmlspecialchars($seo['og_url'] ?? '') ?>">
    <meta property="og:site_name" content="<?= htmlspecialchars($seo['og_site_name'] ?? $siteName) ?>">
    <?php if (!empty($seo['og_image'])): ?>
    <meta property="og:image" content="<?= htmlspecialchars($seo['og_image']) ?>">
    <?php endif; ?>
    <?php endif; ?>

    <!-- Twitter Cards -->
    <?php if (!empty($seo['twitter_card'])): ?>
    <meta name="twitter:card" content="<?= $seo['twitter_card'] ?>">
    <meta name="twitter:title" content="<?= htmlspecialchars($seo['twitter_title'] ?? '') ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($seo['twitter_description'] ?? '') ?>">
    <?php if (!empty($seo['twitter_image'])): ?>
    <meta name="twitter:image" content="<?= htmlspecialchars($seo['twitter_image']) ?>">
    <?php endif; ?>
    <?php if (!empty($seo['twitter_site'])): ?>
    <meta name="twitter:site" content="<?= htmlspecialchars($seo['twitter_site']) ?>">
    <?php endif; ?>
    <?php endif; ?>

    <!-- JSON-LD -->
    <?php if (!empty($seo['schema'])): ?>
    <script type="application/ld+json"><?= $seo['schema'] ?></script>
    <?php endif; ?>

    <!-- Verification -->
    <?php if ($settings->get('google_verification')): ?>
    <meta name="google-site-verification" content="<?= htmlspecialchars($settings->get('google_verification')) ?>">
    <?php endif; ?>
    <?php if ($settings->get('bing_verification')): ?>
    <meta name="msvalidate.01" content="<?= htmlspecialchars($settings->get('bing_verification')) ?>">
    <?php endif; ?>

    <link rel="alternate" type="application/rss+xml" title="<?= htmlspecialchars($siteName) ?> RSS" href="<?= $siteUrl ?>/feed">

    <?php if ($settings->get('site_favicon')): ?>
    <link rel="icon" href="<?= htmlspecialchars($settings->get('site_favicon')) ?>">
    <?php endif; ?>

    <link rel="stylesheet" href="<?= $siteUrl ?>/themes/flavor/assets/css/style.css">

    <?php if ($settings->get('custom_css')): ?>
    <style><?= $settings->get('custom_css') ?></style>
    <?php endif; ?>

    <?php if ($settings->get('header_code')): ?>
    <?= $settings->get('header_code') ?>
    <?php endif; ?>
</head>
<body>

<header class="site-header">
    <div class="container">
        <div class="header-inner">
            <div class="site-brand">
                <?php if ($settings->get('site_logo')): ?>
                <a href="<?= $siteUrl ?>"><img src="<?= htmlspecialchars($settings->get('site_logo')) ?>" alt="<?= htmlspecialchars($siteName) ?>" class="site-logo"></a>
                <?php else: ?>
                <a href="<?= $siteUrl ?>" class="site-title"><?= htmlspecialchars($siteName) ?></a>
                <?php if ($siteTagline): ?>
                <span class="site-tagline"><?= htmlspecialchars($siteTagline) ?></span>
                <?php endif; ?>
                <?php endif; ?>
            </div>

            <button class="mobile-menu-toggle" aria-label="Toggle menu" onclick="document.querySelector('.main-nav').classList.toggle('open')">
                <span></span><span></span><span></span>
            </button>

            <nav class="main-nav">
                <ul>
                    <?php if (empty($menuItems)): ?>
                    <li><a href="<?= $siteUrl ?>">Home</a></li>
                    <?php else: ?>
                    <?php foreach ($menuItems as $item): ?>
                    <li><a href="<?= htmlspecialchars($item['url']) ?>"><?= htmlspecialchars($item['title']) ?></a></li>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </nav>

            <div class="header-search">
                <form action="<?= $siteUrl ?>/search" method="get">
                    <input type="text" name="q" placeholder="Search..." class="search-input" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                </form>
            </div>
        </div>
    </div>
</header>

<main class="site-main">
    <div class="container">
        <?php if (!empty($flash['message'] ?? ($_SESSION['_flash']['message'] ?? ''))): ?>
        <?php $f = $flash ?? $_SESSION['_flash'] ?? []; unset($_SESSION['_flash']); ?>
        <div class="alert alert-<?= $f['type'] ?? 'success' ?>"><?= htmlspecialchars($f['message']) ?></div>
        <?php endif; ?>

        <?= \GlassPress\Core\View::yieldSection('content') ?>
    </div>
</main>

<footer class="site-footer">
    <div class="container">
        <div class="footer-inner">
            <div class="footer-info">
                <?php $footerText = $settings->get('footer_text', ''); ?>
                <?php if ($footerText): ?>
                <p><?= htmlspecialchars($footerText) ?></p>
                <?php else: ?>
                <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($siteName) ?>. Powered by <a href="#">GlassPress</a>.</p>
                <?php endif; ?>
            </div>
            <div class="footer-links">
                <a href="<?= $siteUrl ?>/feed">RSS Feed</a>
                <a href="<?= $siteUrl ?>/sitemap.xml">Sitemap</a>
            </div>
        </div>
    </div>
</footer>

<script src="<?= $siteUrl ?>/themes/flavor/assets/js/main.js"></script>

<?php if ($settings->get('footer_code')): ?>
<?= $settings->get('footer_code') ?>
<?php endif; ?>

</body>
</html>
