<?php
namespace GlassPress\App\Admin\Controllers;

class DashboardController extends AdminController
{
    public function index(): void
    {
        $db = $this->app->getService('db');

        $stats = [
            'posts' => $db->count('posts', "post_type = 'post' AND status != 'trash'"),
            'pages' => $db->count('posts', "post_type = 'page' AND status != 'trash'"),
            'comments' => $db->count('comments', "status != 'trash'"),
            'users' => $db->count('users', '1'),
            'drafts' => $db->count('posts', "status = 'draft'"),
            'pending_comments' => $db->count('comments', "status = 'pending'"),
        ];

        // Recent posts
        $recentPosts = $db->fetchAll(sprintf(
            "SELECT p.*, u.display_name as author_name FROM %s p 
             LEFT JOIN %s u ON p.author_id = u.id 
             WHERE p.post_type = 'post' AND p.status != 'trash' 
             ORDER BY p.created_at DESC LIMIT 5",
            $db->prefix('posts'), $db->prefix('users')
        ));

        // Recent comments
        $recentComments = $db->fetchAll(sprintf(
            "SELECT c.*, p.title as post_title FROM %s c 
             LEFT JOIN %s p ON c.post_id = p.id 
             WHERE c.status != 'trash' 
             ORDER BY c.created_at DESC LIMIT 5",
            $db->prefix('comments'), $db->prefix('posts')
        ));

        $this->render('dashboard.index', [
            'pageTitle' => 'Dashboard',
            'stats' => $stats,
            'recentPosts' => $recentPosts,
            'recentComments' => $recentComments,
        ]);
    }
}
