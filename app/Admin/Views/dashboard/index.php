<?php \GlassPress\Core\View::startSection('content'); ?>

<div class="page-header">
    <h1>Dashboard</h1>
    <div class="page-header-actions">
        <a href="<?= $app->getAdminUrl('posts/create') ?>" class="btn btn-primary">+ New Post</a>
    </div>
</div>

<!-- Stats Grid -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-label">Published Posts</div>
        <div class="stat-value"><?= $stats['posts'] ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Pages</div>
        <div class="stat-value"><?= $stats['pages'] ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Comments</div>
        <div class="stat-value"><?= $stats['comments'] ?></div>
        <?php if ($stats['pending_comments'] > 0): ?>
        <div class="stat-change"><?= $stats['pending_comments'] ?> pending</div>
        <?php endif; ?>
    </div>
    <div class="stat-card">
        <div class="stat-label">Users</div>
        <div class="stat-value"><?= $stats['users'] ?></div>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Recent Posts -->
    <div class="glass-card">
        <div class="glass-card-header">
            <h2>Recent Posts</h2>
            <a href="<?= $app->getAdminUrl('posts') ?>" class="btn btn-sm btn-secondary">View All</a>
        </div>
        <?php if (empty($recentPosts)): ?>
        <div class="empty-state">
            <p>No posts yet. <a href="<?= $app->getAdminUrl('posts/create') ?>">Create your first post</a></p>
        </div>
        <?php else: ?>
        <ul class="widget-list">
            <?php foreach ($recentPosts as $post): ?>
            <li>
                <div>
                    <a href="<?= $app->getAdminUrl('posts/edit/' . $post['id']) ?>" style="color:var(--text-bright);font-weight:500;"><?= htmlspecialchars($post['title'] ?: '(Untitled)') ?></a>
                    <div class="text-xs text-muted"><?= date('M j, Y', strtotime($post['created_at'])) ?> by <?= htmlspecialchars($post['author_name'] ?? 'Unknown') ?></div>
                </div>
                <span class="badge badge-<?= $post['status'] ?>"><?= ucfirst($post['status']) ?></span>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>

    <!-- Sidebar widgets -->
    <div>
        <!-- Quick Draft -->
        <div class="glass-card">
            <div class="glass-card-header">
                <h2>Quick Draft</h2>
            </div>
            <form method="POST" action="<?= $app->getAdminUrl('posts/store') ?>">
                <?= $csrf_field ?>
                <input type="hidden" name="post_type" value="post">
                <input type="hidden" name="status" value="draft">
                <div class="form-group">
                    <input type="text" name="title" class="form-control" placeholder="Post title...">
                </div>
                <div class="form-group">
                    <textarea name="content" class="form-control" rows="3" placeholder="What's on your mind?"></textarea>
                </div>
                <button type="submit" class="btn btn-secondary btn-sm">Save Draft</button>
            </form>
        </div>

        <!-- At a Glance -->
        <div class="glass-card">
            <div class="glass-card-header">
                <h2>At a Glance</h2>
            </div>
            <ul class="widget-list">
                <li><span><?= $stats['drafts'] ?> Drafts</span></li>
                <li><span><?= $stats['pending_comments'] ?> Pending Comments</span></li>
                <li><span>GlassPress <?= GLASSPRESS_VERSION ?></span></li>
                <li><span>PHP <?= PHP_VERSION ?></span></li>
            </ul>
        </div>

        <!-- Recent Comments -->
        <div class="glass-card">
            <div class="glass-card-header">
                <h2>Recent Comments</h2>
            </div>
            <?php if (empty($recentComments)): ?>
            <p class="text-muted text-sm">No comments yet.</p>
            <?php else: ?>
            <ul class="widget-list">
                <?php foreach ($recentComments as $comment): ?>
                <li>
                    <div>
                        <strong class="text-sm"><?= htmlspecialchars($comment['author_name']) ?></strong>
                        <span class="text-muted text-xs">on <?= htmlspecialchars($comment['post_title'] ?? '') ?></span>
                        <div class="text-xs text-muted"><?= htmlspecialchars(mb_substr(strip_tags($comment['content']), 0, 60)) ?>...</div>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php \GlassPress\Core\View::endSection(); ?>
