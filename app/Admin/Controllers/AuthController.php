<?php
namespace GlassPress\App\Admin\Controllers;

use GlassPress\Core\Application;
use GlassPress\Core\CSRF;

/**
 * Authentication controller.
 */
class AuthController
{
    private Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function loginForm(): void
    {
        // Already logged in
        $auth = $this->app->getService('auth');
        if ($auth && $auth->check()) {
            header('Location: ' . $this->app->getAdminUrl());
            exit;
        }

        $data = [
            'csrf_field' => CSRF::field(),
            'error' => $_SESSION['login_error'] ?? '',
            'success' => isset($_GET['installed']) ? 'GlassPress has been installed successfully! Please log in.' : '',
            'redirect' => $_GET['redirect'] ?? '',
            'siteName' => $this->app->getService('settings')->get('site_title', 'GlassPress'),
        ];
        unset($_SESSION['login_error']);

        ob_start();
        extract($data, EXTR_SKIP);
        require GLASSPRESS_ROOT . '/app/Admin/Views/auth/login.php';
        echo ob_get_clean();
    }

    public function login(): void
    {
        $token = $_POST['_csrf_token'] ?? '';
        if (!CSRF::verify($token)) {
            $_SESSION['login_error'] = 'Invalid security token.';
            header('Location: ' . $this->app->getAdminUrl('login'));
            exit;
        }

        $login = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        if (empty($login) || empty($password)) {
            $_SESSION['login_error'] = 'Please enter your username/email and password.';
            header('Location: ' . $this->app->getAdminUrl('login'));
            exit;
        }

        $auth = $this->app->getService('auth');
        
        if ($auth->attempt($login, $password, $remember)) {
            $redirect = $_POST['redirect'] ?? '';
            $target = $redirect && str_starts_with($redirect, '/') ? $redirect : $this->app->getAdminUrl();
            header('Location: ' . $target);
            exit;
        }

        $_SESSION['login_error'] = 'Invalid username/email or password.';
        header('Location: ' . $this->app->getAdminUrl('login'));
        exit;
    }

    public function logout(): void
    {
        $auth = $this->app->getService('auth');
        $auth->logout();
        header('Location: ' . $this->app->getAdminUrl('login'));
        exit;
    }

    public function forgotForm(): void
    {
        $data = [
            'csrf_field' => CSRF::field(),
            'message' => $_SESSION['forgot_message'] ?? '',
            'error' => $_SESSION['forgot_error'] ?? '',
            'siteName' => $this->app->getService('settings')->get('site_title', 'GlassPress'),
        ];
        unset($_SESSION['forgot_message'], $_SESSION['forgot_error']);

        ob_start();
        extract($data, EXTR_SKIP);
        require GLASSPRESS_ROOT . '/app/Admin/Views/auth/forgot.php';
        echo ob_get_clean();
    }

    public function forgot(): void
    {
        $token = $_POST['_csrf_token'] ?? '';
        if (!CSRF::verify($token)) {
            $_SESSION['forgot_error'] = 'Invalid security token.';
            header('Location: ' . $this->app->getAdminUrl('forgot-password'));
            exit;
        }

        $email = trim($_POST['email'] ?? '');

        // Always show success message to prevent email enumeration
        $_SESSION['forgot_message'] = 'If an account with that email exists, a password reset link has been sent.';
        
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $auth = $this->app->getService('auth');
            $token = $auth->createPasswordResetToken($email);
            
            if ($token) {
                // In production, send email. For now, log it.
                $resetUrl = $this->app->getSiteUrl('admin/reset-password?email=' . urlencode($email) . '&token=' . $token);
                error_log("Password reset URL for {$email}: {$resetUrl}");
            }
        }

        header('Location: ' . $this->app->getAdminUrl('forgot-password'));
        exit;
    }
}
