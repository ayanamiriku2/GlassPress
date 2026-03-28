<?php
namespace GlassPress\App\Controllers;

use GlassPress\Core\Application;

class CommentController
{
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function store(): void
    {
        $db = $this->app->getService('db');
        $settings = $this->app->getService('settings');

        // CSRF check
        $token = $_POST['csrf_token'] ?? '';
        if (!\GlassPress\Core\CSRF::verify($token)) {
            http_response_code(403);
            echo 'Invalid CSRF token.';
            return;
        }

        $postId = (int) ($_POST['post_id'] ?? 0);
        $parentId = (int) ($_POST['parent_id'] ?? 0);
        $name = trim($_POST['author_name'] ?? '');
        $email = trim($_POST['author_email'] ?? '');
        $content = trim($_POST['content'] ?? '');

        // Validate
        if (!$postId || !$name || !$email || !$content) {
            $_SESSION['_flash'] = ['message' => 'All fields are required.', 'type' => 'error'];
            $this->redirectBack();
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['_flash'] = ['message' => 'Invalid email address.', 'type' => 'error'];
            $this->redirectBack();
            return;
        }

        // Check post exists and allows comments
        $post = $db->fetch(sprintf(
            "SELECT id, slug, comment_status FROM %s WHERE id = ? AND status = 'publish'",
            $db->prefix('posts')
        ), [$postId]);

        if (!$post || $post['comment_status'] !== 'open') {
            $_SESSION['_flash'] = ['message' => 'Comments are closed for this post.', 'type' => 'error'];
            $this->redirectBack();
            return;
        }

        // Registration required?
        if ($settings->get('comment_registration', '0') === '1') {
            $auth = $this->app->getService('auth');
            if (!$auth->check()) {
                $_SESSION['_flash'] = ['message' => 'You must be logged in to comment.', 'type' => 'error'];
                $this->redirectBack();
                return;
            }
            $user = $auth->user();
            $name = $user['display_name'];
            $email = $user['email'];
        }

        // Determine initial status
        $status = $settings->get('comment_moderation', '1') === '1' ? 'pending' : 'approved';

        // Logged-in users bypass moderation
        $auth = $this->app->getService('auth');
        if ($auth->check()) {
            $status = 'approved';
        }

        $db->insert('comments', [
            'post_id' => $postId,
            'parent_id' => $parentId ?: 0,
            'author_name' => htmlspecialchars(mb_substr($name, 0, 100), ENT_QUOTES, 'UTF-8'),
            'author_email' => $email,
            'author_ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'content' => htmlspecialchars($content, ENT_QUOTES, 'UTF-8'),
            'status' => $status,
            'user_id' => $auth->check() ? $auth->user()['id'] : null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Update comment count
        if ($status === 'approved') {
            // Comment count is computed dynamically, no column to update
        }

        $message = $status === 'pending' ? 'Your comment is awaiting moderation.' : 'Comment posted!';
        $_SESSION['_flash'] = ['message' => $message, 'type' => 'success'];
        header('Location: ' . $this->app->getSiteUrl($post['slug']) . '#comments');
        exit;
    }

    protected function redirectBack(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? $this->app->getSiteUrl();
        header('Location: ' . $referer);
        exit;
    }
}
