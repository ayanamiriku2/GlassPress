<?php
namespace GlassPress\Core;

/**
 * CSRF protection.
 */
class CSRF
{
    public static function generate(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    public static function verify(string $token): bool
    {
        $expected = $_SESSION['_csrf_token'] ?? '';
        if (empty($expected) || empty($token)) {
            return false;
        }
        return hash_equals($expected, $token);
    }

    public static function field(): string
    {
        $token = self::generate();
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function meta(): string
    {
        $token = self::generate();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function regenerate(): void
    {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
}
