<?php \GlassPress\Core\View::startSection('content'); ?>

<article class="single-page">
    <?php if (!empty($page['featured_image'])): ?>
    <div class="post-hero">
        <img src="<?= $siteUrl ?>/<?= htmlspecialchars($page['featured_image']) ?>" alt="<?= htmlspecialchars($page['title']) ?>" class="hero-image">
    </div>
    <?php endif; ?>

    <div class="content-wrap">
        <div class="primary-content page-content">
            <h1 class="page-title"><?= htmlspecialchars($page['title']) ?></h1>

            <div class="post-content prose">
                <?= $page['content'] ?>
            </div>

            <?php if (!empty($comments) || ($page['comment_status'] ?? '') === 'open'): ?>
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
                        <input type="hidden" name="post_id" value="<?= $page['id'] ?>">
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
    </div>
</article>

<?php \GlassPress\Core\View::endSection(); ?>
