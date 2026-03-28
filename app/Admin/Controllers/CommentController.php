<?php
namespace GlassPress\App\Admin\Controllers;

class CommentController extends AdminController
{
    public function index(): void
    {
        $this->requireCapability('moderate_comments');
        $db = $this->app->getService('db');

        $status = $_GET['status'] ?? '';
        $page = max(1, (int) ($_GET['paged'] ?? 1));
        $perPage = 20;

        $where = '1=1';
        $params = [];

        if ($status && in_array($status, ['approved', 'pending', 'spam', 'trash'])) {
            $where .= ' AND c.status = ?';
            $params[] = $status;
        } else {
            $where .= " AND c.status != 'trash'";
        }

        $total = (int) $db->fetchColumn(
            sprintf('SELECT COUNT(*) FROM %s c WHERE %s', $db->prefix('comments'), $where),
            $params
        );

        $comments = $db->fetchAll(sprintf(
            "SELECT c.*, p.title as post_title, p.slug as post_slug 
             FROM %s c 
             LEFT JOIN %s p ON c.post_id = p.id 
             WHERE %s ORDER BY c.created_at DESC LIMIT %d OFFSET %d",
            $db->prefix('comments'), $db->prefix('posts'), $where, $perPage, ($page - 1) * $perPage
        ), $params);

        // Status counts
        $counts = [];
        $countRows = $db->fetchAll(sprintf(
            "SELECT status, COUNT(*) as cnt FROM %s GROUP BY status",
            $db->prefix('comments')
        ));
        foreach ($countRows as $row) {
            $counts[$row['status']] = (int) $row['cnt'];
        }

        $this->render('comments.index', [
            'pageTitle' => 'Comments',
            'comments' => $comments,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'pages' => ceil($total / $perPage),
            'status' => $status,
            'counts' => $counts,
        ]);
    }

    public function approve(string $id): void
    {
        $this->requireCapability('moderate_comments');
        $db = $this->app->getService('db');
        $db->update('comments', ['status' => 'approved'], 'id = ?', [(int) $id]);
        $this->redirect($this->app->getAdminUrl('comments'), 'Comment approved.');
    }

    public function unapprove(string $id): void
    {
        $this->requireCapability('moderate_comments');
        $db = $this->app->getService('db');
        $db->update('comments', ['status' => 'pending'], 'id = ?', [(int) $id]);
        $this->redirect($this->app->getAdminUrl('comments'), 'Comment set to pending.');
    }

    public function spam(string $id): void
    {
        $this->requireCapability('moderate_comments');
        $db = $this->app->getService('db');
        $db->update('comments', ['status' => 'spam'], 'id = ?', [(int) $id]);
        $this->redirect($this->app->getAdminUrl('comments'), 'Comment marked as spam.');
    }

    public function trash(string $id): void
    {
        $this->requireCapability('moderate_comments');
        $db = $this->app->getService('db');
        $db->update('comments', ['status' => 'trash'], 'id = ?', [(int) $id]);
        $this->redirect($this->app->getAdminUrl('comments'), 'Comment trashed.');
    }

    public function delete(string $id): void
    {
        $this->requireCapability('moderate_comments');
        $db = $this->app->getService('db');
        $db->delete('comments', 'id = ?', [(int) $id]);
        $this->redirect($this->app->getAdminUrl('comments?status=trash'), 'Comment permanently deleted.');
    }

    public function reply(string $id): void
    {
        $this->requireCapability('moderate_comments');
        $db = $this->app->getService('db');
        $auth = $this->app->getService('auth');

        $parent = $db->fetch(
            sprintf('SELECT * FROM %s WHERE id = ?', $db->prefix('comments')),
            [(int) $id]
        );

        if (!$parent) {
            $this->redirect($this->app->getAdminUrl('comments'), 'Comment not found.', 'error');
            return;
        }

        $content = $this->sanitizeHtml($_POST['reply_content'] ?? '');
        if (empty(trim(strip_tags($content)))) {
            $this->redirect($this->app->getAdminUrl('comments'), 'Reply cannot be empty.', 'error');
            return;
        }

        $user = $auth->user();
        $db->insert('comments', [
            'post_id' => $parent['post_id'],
            'parent_id' => $parent['id'],
            'author_name' => $user['display_name'],
            'author_email' => $user['email'],
            'author_ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'content' => $content,
            'status' => 'approved',
            'user_id' => $user['id'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Update comment count on post
        // Comment count is computed dynamically, no column to update

        $this->redirect($this->app->getAdminUrl('comments'), 'Reply posted.');
    }

    public function edit(string $id): void
    {
        $this->requireCapability('moderate_comments');
        $db = $this->app->getService('db');

        $comment = $db->fetch(
            sprintf('SELECT c.*, p.title as post_title FROM %s c LEFT JOIN %s p ON c.post_id = p.id WHERE c.id = ?', $db->prefix('comments'), $db->prefix('posts')),
            [(int) $id]
        );

        if (!$comment) {
            $this->redirect($this->app->getAdminUrl('comments'), 'Comment not found.', 'error');
            return;
        }

        $this->render('comments.edit', [
            'pageTitle' => 'Edit Comment',
            'comment' => $comment,
        ]);
    }

    public function update(string $id): void
    {
        $this->requireCapability('moderate_comments');
        $db = $this->app->getService('db');

        $db->update('comments', [
            'author_name' => trim($_POST['author_name'] ?? ''),
            'author_email' => trim($_POST['author_email'] ?? ''),
            'content' => $this->sanitizeHtml($_POST['content'] ?? ''),
            'status' => $_POST['status'] ?? 'pending',
        ], 'id = ?', [(int) $id]);

        $this->redirect($this->app->getAdminUrl('comments'), 'Comment updated.');
    }

    public function bulk(): void
    {
        $this->requireCapability('moderate_comments');
        $action = $_POST['bulk_action'] ?? '';
        $ids = array_map('intval', $_POST['comment_ids'] ?? []);
        $db = $this->app->getService('db');

        if (empty($ids)) {
            $this->redirect($this->app->getAdminUrl('comments'), 'No comments selected.', 'warning');
            return;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        switch ($action) {
            case 'approve':
                $db->query(sprintf("UPDATE %s SET status = 'approved' WHERE id IN (%s)", $db->prefix('comments'), $placeholders), $ids);
                break;
            case 'unapprove':
                $db->query(sprintf("UPDATE %s SET status = 'pending' WHERE id IN (%s)", $db->prefix('comments'), $placeholders), $ids);
                break;
            case 'spam':
                $db->query(sprintf("UPDATE %s SET status = 'spam' WHERE id IN (%s)", $db->prefix('comments'), $placeholders), $ids);
                break;
            case 'trash':
                $db->query(sprintf("UPDATE %s SET status = 'trash' WHERE id IN (%s)", $db->prefix('comments'), $placeholders), $ids);
                break;
        }

        $this->redirect($this->app->getAdminUrl('comments'), count($ids) . ' comments updated.');
    }
}
