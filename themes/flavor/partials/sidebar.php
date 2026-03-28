<aside class="sidebar">
    <!-- Search -->
    <div class="widget glass-card">
        <h4 class="widget-title">Search</h4>
        <form action="<?= $siteUrl ?>/search" method="get">
            <input type="text" name="q" class="form-input" placeholder="Search..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
        </form>
    </div>

    <!-- Recent Posts -->
    <?php if (!empty($recentPosts)): ?>
    <div class="widget glass-card">
        <h4 class="widget-title">Recent Posts</h4>
        <ul class="widget-list">
            <?php foreach ($recentPosts as $rp): ?>
            <li>
                <a href="<?= $siteUrl ?>/<?= $rp['slug'] ?>"><?= htmlspecialchars($rp['title']) ?></a>
                <time><?= date($dateFormat, strtotime($rp['published_at'])) ?></time>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <!-- Categories -->
    <?php if (!empty($categories)): ?>
    <div class="widget glass-card">
        <h4 class="widget-title">Categories</h4>
        <ul class="widget-list">
            <?php foreach ($categories as $cat): ?>
            <li>
                <a href="<?= $siteUrl ?>/<?= $settings->get('category_base', 'category') ?>/<?= $cat['slug'] ?>">
                    <?= htmlspecialchars($cat['name']) ?>
                    <span class="count">(<?= $cat['count'] ?>)</span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <!-- Tags Cloud -->
    <?php if (!empty($tags)): ?>
    <div class="widget glass-card">
        <h4 class="widget-title">Tags</h4>
        <div class="tag-cloud">
            <?php foreach ($tags as $tag): ?>
            <a href="<?= $siteUrl ?>/<?= $settings->get('tag_base', 'tag') ?>/<?= $tag['slug'] ?>" class="tag-pill"><?= htmlspecialchars($tag['name']) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</aside>
