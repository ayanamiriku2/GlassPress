<div class="comment <?= $depth > 0 ? 'comment-reply' : '' ?>" id="comment-<?= $comment['id'] ?>" style="margin-left:<?= min($depth * 32, 96) ?>px">
    <div class="comment-header">
        <img src="https://www.gravatar.com/avatar/<?= md5(strtolower(trim($comment['author_email'] ?? ''))) ?>?s=40&d=mp" alt="" class="comment-avatar">
        <div>
            <strong class="comment-author"><?= htmlspecialchars($comment['author_name']) ?></strong>
            <time class="comment-date"><?= date($dateFormat, strtotime($comment['created_at'])) ?></time>
        </div>
    </div>
    <div class="comment-body">
        <p><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
    </div>
    <div class="comment-actions">
        <a href="#respond" class="reply-link" onclick="document.getElementById('comment-parent-id').value='<?= $comment['id'] ?>';this.textContent='Replying to <?= htmlspecialchars($comment['author_name'], ENT_QUOTES) ?>';return true;">Reply</a>
    </div>

    <?php if (!empty($comment['children'])): ?>
    <?php foreach ($comment['children'] as $child): ?>
    <?php \GlassPress\Core\View::partial('theme.partials.comment', array_merge(get_defined_vars(), ['comment' => $child, 'depth' => $depth + 1])); ?>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
