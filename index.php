<?php
/**
 * GlassPress - Modern PHP CMS for Shared Hosting
 * 
 * Front controller - all requests route through here.
 */

// Minimum PHP version check
if (version_compare(PHP_VERSION, '8.2.0', '<')) {
    http_response_code(500);
    echo '<!DOCTYPE html><html><head><title>GlassPress - PHP Version Required</title></head><body>';
    echo '<h1>PHP 8.2+ Required</h1><p>Your server is running PHP ' . PHP_VERSION . '. GlassPress requires PHP 8.2 or higher.</p>';
    echo '</body></html>';
    exit(1);
}

define('GLASSPRESS_START', microtime(true));
define('GLASSPRESS_ROOT', __DIR__);
define('GLASSPRESS_VERSION', '1.0.0');

// Serve static files directly when using PHP built-in server
if (PHP_SAPI === 'cli-server') {
    $requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $filePath = GLASSPRESS_ROOT . $requestPath;
    if ($requestPath !== '/' && is_file($filePath)) {
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'webp' => 'image/webp',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject',
            'map' => 'application/json',
        ];
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if (isset($mimeTypes[$ext])) {
            header('Content-Type: ' . $mimeTypes[$ext]);
            readfile($filePath);
            return;
        }
        return false; // Let PHP built-in server handle it
    }
}

// Load autoloader
require_once GLASSPRESS_ROOT . '/core/Autoloader.php';
\GlassPress\Core\Autoloader::register();

// Load Composer autoloader if exists
if (file_exists(GLASSPRESS_ROOT . '/vendor/autoload.php')) {
    require_once GLASSPRESS_ROOT . '/vendor/autoload.php';
}

// Bootstrap the application
$app = \GlassPress\Core\Application::getInstance();
$app->boot();
$app->handleRequest();
