<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <?= \GlassPress\Core\CSRF::meta() ?>
    <title><?= htmlspecialchars($pageTitle ?? 'Admin') ?> - <?= htmlspecialchars($siteName) ?></title>
    <?php $favicon = $settings->get('site_favicon', ''); if ($favicon):
        $favExt = strtolower(pathinfo($favicon, PATHINFO_EXTENSION));
        $favType = match($favExt) { 'png' => 'image/png', 'svg' => 'image/svg+xml', 'gif' => 'image/gif', 'jpg', 'jpeg' => 'image/jpeg', 'webp' => 'image/webp', default => 'image/x-icon' };
    ?>
    <link rel="icon" type="<?= $favType ?>" href="<?= htmlspecialchars($favicon) ?>">
    <link rel="apple-touch-icon" href="<?= htmlspecialchars($favicon) ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= $app->getAssetUrl('css/admin.css') ?>">
</head>
<body class="admin-body">
    <!-- Top Toolbar -->
    <header class="admin-toolbar">
        <div class="toolbar-left">
            <button class="sidebar-toggle" onclick="document.body.classList.toggle('sidebar-collapsed')" title="Toggle Sidebar">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
            </button>
            <a href="<?= $app->getBasePath() ?: '/' ?>" class="toolbar-site-link" target="_blank" title="View Site">
                <?= htmlspecialchars($siteName) ?>
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6M15 3h6v6M10 14L21 3"/></svg>
            </a>
        </div>
        <div class="toolbar-right">
            <div class="toolbar-user">
                <span class="user-avatar"><?= strtoupper(substr($user['display_name'] ?? $user['username'], 0, 1)) ?></span>
                <div class="user-dropdown">
                    <a href="<?= $app->getAdminUrl('profile') ?>">Profile</a>
                    <a href="<?= $app->getAdminUrl('logout') ?>">Sign Out</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="sidebar-logo">
            <a href="<?= $app->getAdminUrl() ?>">
                <span class="logo-icon">&#9670;</span>
                <span class="logo-text">GlassPress</span>
            </a>
        </div>

        <nav class="sidebar-nav">
            <?php
            $uri = $currentUri ?? '';
            $au = function(string $p = '') use ($app) { return $app->getAdminUrl($p); };
            $menuItems = [
                ['url' => $au(), 'icon' => 'dashboard', 'label' => 'Dashboard', 'match' => '/admin$'],
                ['url' => $au('posts'), 'icon' => 'posts', 'label' => 'Posts', 'match' => '/admin/posts', 'children' => [
                    ['url' => $au('posts'), 'label' => 'All Posts'],
                    ['url' => $au('posts/create'), 'label' => 'Add New'],
                    ['url' => $au('categories'), 'label' => 'Categories'],
                    ['url' => $au('tags'), 'label' => 'Tags'],
                ]],
                ['url' => $au('media'), 'icon' => 'media', 'label' => 'Media', 'match' => '/admin/media'],
                ['url' => $au('pages'), 'icon' => 'pages', 'label' => 'Pages', 'match' => '/admin/pages', 'children' => [
                    ['url' => $au('pages'), 'label' => 'All Pages'],
                    ['url' => $au('pages/create'), 'label' => 'Add New'],
                ]],
                ['url' => $au('comments'), 'icon' => 'comments', 'label' => 'Comments', 'match' => '/admin/comments', 'badge' => $pendingComments > 0 ? $pendingComments : null],
                ['url' => $au('menus'), 'icon' => 'menus', 'label' => 'Menus', 'match' => '/admin/menus'],
                ['url' => $au('users'), 'icon' => 'users', 'label' => 'Users', 'match' => '/admin/users'],
                ['url' => $au('settings'), 'icon' => 'settings', 'label' => 'Settings', 'match' => '/admin/settings', 'children' => [
                    ['url' => $au('settings/general'), 'label' => 'General'],
                    ['url' => $au('settings/writing'), 'label' => 'Writing'],
                    ['url' => $au('settings/reading'), 'label' => 'Reading'],
                    ['url' => $au('settings/discussion'), 'label' => 'Discussion'],
                    ['url' => $au('settings/media'), 'label' => 'Media'],
                    ['url' => $au('settings/permalinks'), 'label' => 'Permalinks'],
                    ['url' => $au('settings/seo'), 'label' => 'SEO'],
                    ['url' => $au('settings/redirects'), 'label' => 'Redirects'],
                    ['url' => $au('settings/appearance'), 'label' => 'Appearance'],
                    ['url' => $au('settings/advanced'), 'label' => 'Advanced'],
                ]],
                ['url' => $au('tools'), 'icon' => 'tools', 'label' => 'Tools', 'match' => '/admin/tools', 'children' => [
                    ['url' => $au('tools'), 'label' => 'Overview'],
                    ['url' => $au('tools/seo-health'), 'label' => 'SEO Health'],
                    ['url' => $au('tools/404-log'), 'label' => '404 Log'],
                    ['url' => $au('tools/system-info'), 'label' => 'System Info'],
                ]],
            ];

            $icons = [
                'dashboard' => '<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>',
                'posts' => '<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14 3v4a1 1 0 001 1h4"/><path d="M17 21H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/><path d="M9 9h1M9 13h6M9 17h6"/></svg>',
                'media' => '<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>',
                'pages' => '<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14 3v4a1 1 0 001 1h4M17 21H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/></svg>',
                'comments' => '<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>',
                'menus' => '<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M3 12h18M3 6h18M3 18h18"/></svg>',
                'users' => '<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>',
                'settings' => '<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 01-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>',
                'tools' => '<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>',
            ];

            foreach ($menuItems as $item):
                $isActive = isset($item['match']) && preg_match('#' . $item['match'] . '#', $uri);
                $hasChildren = !empty($item['children']);
                $isOpen = $isActive || ($hasChildren && array_filter($item['children'], fn($c) => str_starts_with($uri, $c['url'])));
            ?>
            <div class="nav-item<?= $isActive || $isOpen ? ' active' : '' ?><?= $hasChildren ? ' has-children' : '' ?>">
                <a href="<?= $item['url'] ?>" class="nav-link">
                    <span class="nav-icon"><?= $icons[$item['icon']] ?? '' ?></span>
                    <span class="nav-label"><?= $item['label'] ?></span>
                    <?php if (!empty($item['badge'])): ?>
                    <span class="nav-badge"><?= $item['badge'] ?></span>
                    <?php endif; ?>
                    <?php if ($hasChildren): ?>
                    <span class="nav-arrow">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
                    </span>
                    <?php endif; ?>
                </a>
                <?php if ($hasChildren): ?>
                <div class="nav-children<?= $isOpen ? ' open' : '' ?>">
                    <?php foreach ($item['children'] as $child): ?>
                    <a href="<?= $child['url'] ?>" class="nav-child-link<?= $uri === $child['url'] ? ' active' : '' ?>"><?= $child['label'] ?></a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
        <?php if (!empty($flash['message'])): ?>
        <div class="toast toast-<?= $flash['type'] ?? 'success' ?>" id="flash-toast">
            <span><?= htmlspecialchars($flash['message']) ?></span>
            <button onclick="this.parentElement.remove()" class="toast-close">&times;</button>
        </div>
        <?php endif; ?>

        <?= \GlassPress\Core\View::yieldSection('content') ?>
    </main>

    <script>window.GP_BASE_PATH = <?= json_encode($app->getBasePath()) ?>;</script>
    <script src="<?= $app->getAssetUrl('js/admin.js') ?>"></script>

    <!-- Media Library Modal -->
    <div class="modal-overlay" id="media-modal">
        <div class="modal" style="max-width:900px;max-height:90vh;display:flex;flex-direction:column">
            <div class="modal-header">
                <h3>Media Library</h3>
                <button class="modal-close" onclick="closeModal('media-modal')">&times;</button>
            </div>
            <div style="display:flex;gap:0;border-bottom:1px solid var(--glass-border)">
                <button type="button" class="media-tab active" data-tab="browse" onclick="gpMedia.switchTab('browse')">Browse</button>
                <button type="button" class="media-tab" data-tab="upload" onclick="gpMedia.switchTab('upload')">Upload</button>
            </div>
            <div class="modal-body" style="flex:1;overflow-y:auto;min-height:300px">
                <!-- Browse Tab -->
                <div id="media-tab-browse">
                    <div id="media-grid-container" class="media-picker-grid"></div>
                    <div id="media-load-more" style="text-align:center;padding:1rem;display:none">
                        <button type="button" class="btn btn-default btn-sm" onclick="gpMedia.loadMore()">Load More</button>
                    </div>
                    <div id="media-empty" style="text-align:center;padding:2rem;color:var(--text-muted);display:none">
                        No images found. Upload one!
                    </div>
                </div>
                <!-- Upload Tab -->
                <div id="media-tab-upload" style="display:none">
                    <div id="media-upload-zone" class="media-upload-zone">
                        <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="opacity:.4"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
                        <p style="margin:.5rem 0 0;opacity:.6">Drag & drop files here or click to browse</p>
                        <input type="file" id="media-upload-input" accept="image/*" multiple style="display:none">
                    </div>
                    <div id="media-upload-progress" style="margin-top:1rem"></div>
                </div>
            </div>
            <div class="modal-footer">
                <div id="media-selected-info" style="flex:1;font-size:13px;color:var(--text-muted)"></div>
                <button type="button" class="btn btn-default" onclick="closeModal('media-modal')">Cancel</button>
                <button type="button" class="btn btn-primary" id="media-insert-btn" onclick="gpMedia.insertSelected()" disabled>Select</button>
            </div>
        </div>
    </div>
    <script src="<?= $app->getAssetUrl('js/media-modal.js') ?>"></script>

    <?php if (isset($scripts)): ?>
    <?php foreach ((array)$scripts as $script): ?>
    <script src="<?= $script ?>"></script>
    <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
