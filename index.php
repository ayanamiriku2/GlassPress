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
