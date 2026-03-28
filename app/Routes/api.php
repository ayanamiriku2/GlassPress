<?php
/**
 * GlassPress API Routes
 * 
 * @var \GlassPress\Core\Router $router
 * @var \GlassPress\Core\Application $app
 */

// JSON API endpoints for AJAX operations
$router->group(['prefix' => 'api', 'middleware' => ['auth']], function ($router) {
    // Media upload (AJAX)
    $router->post('/media/upload', 'GlassPress\App\Admin\Controllers\MediaController@apiUpload', 'api.media.upload');
    $router->get('/media/list', 'GlassPress\App\Admin\Controllers\MediaController@apiList', 'api.media.list');

    // Autosave
    $router->post('/posts/autosave', 'GlassPress\App\Admin\Controllers\PostController@apiAutosave', 'api.posts.autosave');

    // Slug generation
    $router->get('/slug', 'GlassPress\App\Admin\Controllers\PostController@generateSlugApi', 'api.slug');
    
    // Link checker
    $router->post('/check-links', 'GlassPress\App\Admin\Controllers\ToolsController@checkLinks', 'api.check_links');
});
