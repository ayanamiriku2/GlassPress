<?php
namespace GlassPress\Core;

/**
 * Authentication and session management.
 */
class Auth
{
    private Database $db;
    private ?array $currentUser = null;
    private ?array $capabilities = null;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->loadSession();
    }

    private function loadSession(): void
    {
        if (!empty($_SESSION['gp_user_id'])) {
            $user = $this->db->fetch(
                sprintf('SELECT * FROM %s WHERE id = ? AND status = ?', $this->db->prefix('users')),
                [$_SESSION['gp_user_id'], 'active']
            );
            
            if ($user) {
                unset($user['password']);
                $this->currentUser = $user;
                $this->loadCapabilities($user['role']);
            } else {
                $this->logout();
            }
        }
    }

    private function loadCapabilities(string $role): void
    {
        $this->capabilities = self::getRoleCapabilities($role);
    }

    public static function getRoleCapabilities(string $role): array
    {
        $roles = [
            'administrator' => [
                'access_admin', 'manage_settings', 'manage_options', 'manage_users', 'manage_roles',
                'list_users', 'create_users', 'edit_users', 'delete_users',
                'create_posts', 'edit_posts', 'edit_others_posts', 'delete_posts', 'delete_others_posts',
                'publish_posts', 'manage_categories', 'manage_tags',
                'create_pages', 'edit_pages', 'edit_others_pages', 'delete_pages',
                'manage_media', 'upload_files', 'manage_comments', 'moderate_comments',
                'manage_menus', 'manage_themes', 'manage_seo', 'manage_redirects',
                'manage_tools', 'view_analytics', 'export_content', 'import_content',
                'manage_plugins', 'edit_custom_css',
            ],
            'editor' => [
                'access_admin', 'create_posts', 'edit_posts', 'edit_others_posts',
                'delete_posts', 'delete_others_posts', 'publish_posts',
                'manage_categories', 'manage_tags',
                'create_pages', 'edit_pages', 'edit_others_pages', 'delete_pages',
                'manage_media', 'upload_files', 'manage_comments', 'moderate_comments',
                'manage_menus', 'list_users',
            ],
            'author' => [
                'access_admin', 'create_posts', 'edit_posts', 'delete_posts',
                'publish_posts', 'upload_files', 'manage_media',
            ],
            'contributor' => [
                'access_admin', 'create_posts', 'edit_posts', 'delete_posts',
            ],
            'subscriber' => [
                'read',
            ],
        ];

        return $roles[$role] ?? $roles['subscriber'];
    }

    public function attempt(string $login, string $password, bool $remember = false): bool
    {
        $user = $this->db->fetch(
            sprintf(
                'SELECT * FROM %s WHERE (username = ? OR email = ?) AND status = ?',
                $this->db->prefix('users')
            ),
            [$login, $login, 'active']
        );

        if (!$user || !password_verify($password, $user['password'])) {
            // Log failed attempt
            $this->logLoginAttempt($login, false);
            return false;
        }

        // Check brute force
        if ($this->isLockedOut($login)) {
            return false;
        }

        // Regenerate session ID
        session_regenerate_id(true);

        $_SESSION['gp_user_id'] = $user['id'];
        $_SESSION['gp_login_time'] = time();

        // Update last login
        $this->db->update('users', [
            'last_login' => date('Y-m-d H:i:s'),
            'login_ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        ], 'id = ?', [$user['id']]);

        unset($user['password']);
        $this->currentUser = $user;
        $this->loadCapabilities($user['role']);

        $this->logLoginAttempt($login, true);

        return true;
    }

    public function logout(): void
    {
        $this->currentUser = null;
        $this->capabilities = null;
        
        $_SESSION = [];
        
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        
        session_destroy();
        session_start();
        session_regenerate_id(true);
    }

    public function check(): bool
    {
        return $this->currentUser !== null;
    }

    public function user(): ?array
    {
        return $this->currentUser;
    }

    public function userId(): ?int
    {
        return $this->currentUser['id'] ?? null;
    }

    public function hasCapability(string $capability): bool
    {
        return $this->capabilities !== null && in_array($capability, $this->capabilities, true);
    }

    public function hasRole(string $role): bool
    {
        return $this->currentUser !== null && $this->currentUser['role'] === $role;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('administrator');
    }

    private function logLoginAttempt(string $login, bool $success): void
    {
        try {
            $this->db->insert('audit_log', [
                'action' => $success ? 'login_success' : 'login_failed',
                'target_type' => 'auth',
                'target_id' => 0,
                'user_id' => $this->currentUser['id'] ?? 0,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
                'details' => json_encode(['login' => $login]),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            // Audit log table might not exist yet
        }
    }

    private function isLockedOut(string $login): bool
    {
        try {
            $since = date('Y-m-d H:i:s', time() - 900); // 15 minutes
            $attempts = $this->db->fetchColumn(
                sprintf(
                    "SELECT COUNT(*) FROM %s WHERE action = 'login_failed' AND ip_address = ? AND created_at > ?",
                    $this->db->prefix('audit_log')
                ),
                [$_SERVER['REMOTE_ADDR'] ?? '', $since]
            );
            return $attempts >= 10;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Generate a password reset token.
     */
    public function createPasswordResetToken(string $email): ?string
    {
        $user = $this->db->fetch(
            sprintf('SELECT id FROM %s WHERE email = ? AND status = ?', $this->db->prefix('users')),
            [$email, 'active']
        );

        if (!$user) {
            return null;
        }

        $token = bin2hex(random_bytes(32));
        
        $this->db->update('users', [
            'reset_token' => password_hash($token, PASSWORD_DEFAULT),
            'reset_token_expires' => date('Y-m-d H:i:s', time() + 3600),
        ], 'id = ?', [$user['id']]);

        return $token;
    }

    /**
     * Verify and consume a password reset token.
     */
    public function verifyResetToken(string $email, string $token): ?array
    {
        $user = $this->db->fetch(
            sprintf(
                'SELECT * FROM %s WHERE email = ? AND reset_token IS NOT NULL AND reset_token_expires > ?',
                $this->db->prefix('users')
            ),
            [$email, date('Y-m-d H:i:s')]
        );

        if (!$user || !password_verify($token, $user['reset_token'])) {
            return null;
        }

        return $user;
    }

    /**
     * Hash a password.
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
    }
}
