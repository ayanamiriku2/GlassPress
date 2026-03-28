<?php \GlassPress\Core\View::startSection('content'); ?>

<article class="single-post">
    <?php if (!empty($post['featured_image'])): ?>
    <div class="post-hero">
        <img src="<?= $siteUrl ?>/<?= htmlspecialchars($post['featured_image']) ?>" alt="<?= htmlspecialchars($post['featured_image_alt'] ?? $post['title']) ?>" class="hero-image">
    </div>
    <?php endif; ?>

    <div class="content-wrap <?= ($settings->get('show_sidebar', '1') === '1') ? 'has-sidebar' : '' ?>">
        <div class="primary-content">
            <header class="post-header">
                <div class="post-meta">
                    <?php if (!empty($post['categories'])): ?>
                    <?php foreach ($post['categories'] as $cat): ?>
                    <a href="<?= $siteUrl ?>/<?= $settings->get('category_base', 'category') ?>/<?= $cat['slug'] ?>" class="post-category"><?= htmlspecialchars($cat['name']) ?></a>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <h1 class="post-title"><?= htmlspecialchars($post['title']) ?></h1>

                <div class="post-info">
                    <?php if ($settings->get('show_post_date', '1') === '1'): ?>
                    <time datetime="<?= $post['published_at'] ?? $post['created_at'] ?>">
                        <?= date($dateFormat, strtotime($post['published_at'] ?? $post['created_at'])) ?>
                    </time>
                    <?php endif; ?>

                    <span class="sep">&middot;</span>
                    <span>By <?= htmlspecialchars($post['author_name'] ?? 'Unknown') ?></span>

                    <?php if ($settings->get('show_reading_time', '0') === '1'): ?>
                    <span class="sep">&middot;</span>
                    <span><?= ceil(str_word_count(strip_tags($post['content'])) / 200) ?> min read</span>
                    <?php endif; ?>
                </div>
            </header>

            <div class="post-content prose">
                <?= $post['content'] ?>
            </div>

            <?php if (!empty($post['tags'])): ?>
            <div class="post-tags">
                <?php foreach ($post['tags'] as $tag): ?>
                <a href="<?= $siteUrl ?>/<?= $settings->get('tag_base', 'tag') ?>/<?= $tag['slug'] ?>" class="tag-pill">#<?= htmlspecialchars($tag['name']) ?></a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if ($settings->get('show_author_bio', '1') === '1' && !empty($author)): ?>
            <div class="author-box glass-card">
                <div class="author-box-avatar">
                    <img src="https://www.gravatar.com/avatar/<?= md5(strtolower(trim($author['email'] ?? ''))) ?>?s=80&d=mp" alt="<?= htmlspecialchars($author['display_name']) ?>">
                </div>
                <div class="author-box-info">
                    <h4>Written by <a href="<?= $siteUrl ?>/author/<?= $author['username'] ?>"><?= htmlspecialchars($author['display_name']) ?></a></h4>
                    <?php if (!empty($author['bio'])): ?>
                    <p><?= htmlspecialchars($author['bio']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($relatedPosts)): ?>
            <section class="related-posts">
                <h3>Related Posts</h3>
                <div class="related-grid">
                    <?php foreach ($relatedPosts as $rp): ?>
                    <a href="<?= $siteUrl ?>/<?= $rp['slug'] ?>" class="related-card glass-card">
                        <?php if (!empty($rp['featured_image'])): ?>
                        <img src="<?= $siteUrl ?>/<?= htmlspecialchars($rp['featured_image']) ?>" alt="<?= htmlspecialchars($rp['title']) ?>" loading="lazy">
                        <?php endif; ?>
                        <h4><?= htmlspecialchars($rp['title']) ?></h4>
                        <time><?= date($dateFormat, strtotime($rp['published_at'])) ?></time>
                    </a>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <?php if ($post['comment_status'] === 'open'): ?>
            <section class="comments-section" id="comments">
                <h3><?= $commentCount ?> Comment<?= $commentCount !== 1 ? 's' : '' ?></h3>

                <?php if (!empty($comments)): ?>
                <div class="comment-list">
                    <?php foreach ($comments as $comment): ?>
                    <?php \GlassPress\Core\View::partial('theme.partials.comment', array_merge(get_defined_vars(), ['comment' => $comment, 'depth' => 0])); ?>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div class="comment-form-wrap glass-card" id="respond">
                    <h4>Leave a Comment</h4>
                    <form method="post" action="<?= $siteUrl ?>/comment" class="comment-form">
                        <input type="hidden" name="csrf_token" value="<?= \GlassPress\Core\CSRF::generate() ?>">
                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                        <input type="hidden" name="parent_id" value="0" id="comment-parent-id">

                        <div class="form-row">
                            <div class="form-group">
                                <label>Name *</label>
                                <input type="text" name="author_name" class="form-input" required>
                            </div>
                            <div class="form-group">
                                <label>Email *</label>
                                <input type="email" name="author_email" class="form-input" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Comment *</label>
                            <textarea name="content" class="form-input" rows="5" required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Post Comment</button>
                    </form>
                </div>
            </section>
            <?php endif; ?>
        </div>

        <?php if ($settings->get('show_sidebar', '1') === '1'): ?>
        <?php \GlassPress\Core\View::partial('theme.partials.sidebar', get_defined_vars()); ?>
        <?php endif; ?>
    </div>
</article>

<?php \GlassPress\Core\View::endSection(); ?>
