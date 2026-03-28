<?php
namespace GlassPress\App\Admin\Controllers;

class UserController extends AdminController
{
    public function index(): void
    {
        $this->requireCapability('list_users');
        $db = $this->app->getService('db');

        $role = $_GET['role'] ?? '';
        $search = trim($_GET['s'] ?? '');
        $page = max(1, (int) ($_GET['paged'] ?? 1));
        $perPage = 20;

        $where = '1=1';
        $params = [];

        if ($role) {
            $where .= ' AND role = ?';
            $params[] = $role;
        }

        if ($search) {
            $where .= ' AND (username LIKE ? OR email LIKE ? OR display_name LIKE ?)';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }

        $total = (int) $db->fetchColumn(
            sprintf('SELECT COUNT(*) FROM %s WHERE %s', $db->prefix('users'), $where),
            $params
        );

        $users = $db->fetchAll(sprintf(
            "SELECT * FROM %s WHERE %s ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $db->prefix('users'), $where, $perPage, ($page - 1) * $perPage
        ), $params);

        $roleCounts = [];
        $countRows = $db->fetchAll(sprintf("SELECT role, COUNT(*) as cnt FROM %s GROUP BY role", $db->prefix('users')));
        foreach ($countRows as $row) {
            $roleCounts[$row['role']] = (int) $row['cnt'];
        }

        $this->render('users.index', [
            'pageTitle' => 'Users',
            'users' => $users,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'pages' => ceil($total / $perPage),
            'role' => $role,
            'search' => $search,
            'roleCounts' => $roleCounts,
        ]);
    }

    public function create(): void
    {
        $this->requireCapability('create_users');
        $this->render('users.editor', [
            'pageTitle' => 'Add New User',
            'editUser' => null,
            'isNew' => true,
        ]);
    }

    public function store(): void
    {
        $this->requireCapability('create_users');
        $db = $this->app->getService('db');

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $displayName = trim($_POST['display_name'] ?? '') ?: $username;
        $role = $_POST['role'] ?? 'subscriber';

        // Validate
        if (empty($username) || empty($email) || empty($password)) {
            $this->redirect($this->app->getAdminUrl('users/create'), 'All fields are required.', 'error');
            return;
        }

        if (strlen($password) < 8) {
            $this->redirect($this->app->getAdminUrl('users/create'), 'Password must be at least 8 characters.', 'error');
            return;
        }

        // Check uniqueness
        if ($db->exists('users', 'username = ?', [$username])) {
            $this->redirect($this->app->getAdminUrl('users/create'), 'Username already exists.', 'error');
            return;
        }

        if ($db->exists('users', 'email = ?', [$email])) {
            $this->redirect($this->app->getAdminUrl('users/create'), 'Email already in use.', 'error');
            return;
        }

        $db->insert('users', [
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
            'display_name' => $displayName,
            'role' => $role,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->redirect($this->app->getAdminUrl('users'), 'User created successfully.');
    }

    public function edit(string $id): void
    {
        $db = $this->app->getService('db');
        $auth = $this->app->getService('auth');
        $userId = (int) $id;

        // Users can edit their own profile, admins can edit anyone
        if ($userId !== $auth->userId()) {
            $this->requireCapability('edit_users');
        }

        $editUser = $db->fetch(
            sprintf('SELECT * FROM %s WHERE id = ?', $db->prefix('users')),
            [$userId]
        );

        if (!$editUser) {
            $this->redirect($this->app->getAdminUrl('users'), 'User not found.', 'error');
            return;
        }

        $this->render('users.editor', [
            'pageTitle' => 'Edit User',
            'editUser' => $editUser,
            'isNew' => false,
        ]);
    }

    public function update(string $id): void
    {
        $db = $this->app->getService('db');
        $auth = $this->app->getService('auth');
        $userId = (int) $id;

        if ($userId !== $auth->userId()) {
            $this->requireCapability('edit_users');
        }

        $email = trim($_POST['email'] ?? '');
        $displayName = trim($_POST['display_name'] ?? '');
        $bio = $this->sanitize($_POST['bio'] ?? '');

        $data = [
            'email' => $email,
            'display_name' => $displayName,
            'bio' => $bio,
        ];

        // Only admins can change roles
        if ($auth->hasCapability('edit_users') && isset($_POST['role'])) {
            // Prevent self-demotion for last admin
            if ($userId === $auth->userId() && $_POST['role'] !== 'administrator') {
                $adminCount = $db->count('users', "role = 'administrator'");
                if ($adminCount <= 1) {
                    $this->redirect($this->app->getAdminUrl("users/edit/{$userId}"), 'Cannot remove the last administrator.', 'error');
                    return;
                }
            }
            $data['role'] = $_POST['role'];
        }

        if ($auth->hasCapability('edit_users') && isset($_POST['status'])) {
            $data['status'] = $_POST['status'];
        }

        // Password change
        $newPassword = $_POST['new_password'] ?? '';
        if (!empty($newPassword)) {
            if (strlen($newPassword) < 8) {
                $this->redirect($this->app->getAdminUrl("users/edit/{$userId}"), 'Password must be at least 8 characters.', 'error');
                return;
            }
            $data['password'] = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        }

        // Check email uniqueness
        $existing = $db->fetch(
            sprintf('SELECT id FROM %s WHERE email = ? AND id != ?', $db->prefix('users')),
            [$email, $userId]
        );
        if ($existing) {
            $this->redirect($this->app->getAdminUrl("users/edit/{$userId}"), 'Email already in use.', 'error');
            return;
        }

        $db->update('users', $data, 'id = ?', [$userId]);

        $this->redirect($this->app->getAdminUrl("users/edit/{$userId}"), 'User updated.');
    }

    public function delete(string $id): void
    {
        $this->requireCapability('delete_users');
        $db = $this->app->getService('db');
        $auth = $this->app->getService('auth');
        $userId = (int) $id;

        if ($userId === $auth->userId()) {
            $this->redirect($this->app->getAdminUrl('users'), 'Cannot delete your own account.', 'error');
            return;
        }

        // Reassign content to current user
        $db->query(
            sprintf("UPDATE %s SET author_id = ? WHERE author_id = ?", $db->prefix('posts')),
            [$auth->userId(), $userId]
        );

        $db->delete('users', 'id = ?', [$userId]);

        $this->redirect($this->app->getAdminUrl('users'), 'User deleted. Content reassigned to you.');
    }

    public function profile(): void
    {
        $auth = $this->app->getService('auth');
        $this->edit((string) $auth->userId());
    }
}
