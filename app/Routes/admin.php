<?php
/**
 * GlassPress Admin Routes
 * 
 * @var \GlassPress\Core\Router $router
 * @var \GlassPress\Core\Application $app
 */

// Auth routes (no auth required)
$router->group(['prefix' => 'admin'], function ($router) {
    $router->get('/login', 'GlassPress\App\Admin\Controllers\AuthController@loginForm', 'admin.login');
    $router->post('/login', 'GlassPress\App\Admin\Controllers\AuthController@login', 'admin.login.post');
    $router->get('/logout', 'GlassPress\App\Admin\Controllers\AuthController@logout', 'admin.logout');
    $router->get('/forgot-password', 'GlassPress\App\Admin\Controllers\AuthController@forgotForm', 'admin.forgot');
    $router->post('/forgot-password', 'GlassPress\App\Admin\Controllers\AuthController@forgot', 'admin.forgot.post');
});

// Protected admin routes
$router->group(['prefix' => 'admin', 'middleware' => ['auth', 'csrf']], function ($router) {
    // Dashboard
    $router->get('', 'GlassPress\App\Admin\Controllers\DashboardController@index', 'admin.dashboard');

    // Posts
    $router->get('/posts', 'GlassPress\App\Admin\Controllers\PostController@index', 'admin.posts');
    $router->get('/posts/create', 'GlassPress\App\Admin\Controllers\PostController@create', 'admin.posts.create');
    $router->post('/posts/store', 'GlassPress\App\Admin\Controllers\PostController@store', 'admin.posts.store');
    $router->get('/posts/edit/{id}', 'GlassPress\App\Admin\Controllers\PostController@edit', 'admin.posts.edit');
    $router->post('/posts/update/{id}', 'GlassPress\App\Admin\Controllers\PostController@update', 'admin.posts.update');
    $router->post('/posts/delete/{id}', 'GlassPress\App\Admin\Controllers\PostController@delete', 'admin.posts.delete');
    $router->post('/posts/bulk', 'GlassPress\App\Admin\Controllers\PostController@bulk', 'admin.posts.bulk');
    $router->post('/posts/duplicate/{id}', 'GlassPress\App\Admin\Controllers\PostController@duplicate', 'admin.posts.duplicate');
    $router->post('/posts/autosave', 'GlassPress\App\Admin\Controllers\PostController@autosave', 'admin.posts.autosave');
    $router->get('/posts/revisions/{id}', 'GlassPress\App\Admin\Controllers\PostController@revisions', 'admin.posts.revisions');
    $router->post('/posts/restore-revision/{id}', 'GlassPress\App\Admin\Controllers\PostController@restoreRevision', 'admin.posts.restore_revision');
    $router->post('/posts/revisions/restore/{id}', 'GlassPress\App\Admin\Controllers\PostController@restoreRevision', 'admin.posts.restore_revision_alt');

    // Pages
    $router->get('/pages', 'GlassPress\App\Admin\Controllers\PageController@index', 'admin.pages');
    $router->get('/pages/create', 'GlassPress\App\Admin\Controllers\PageController@create', 'admin.pages.create');
    $router->post('/pages/store', 'GlassPress\App\Admin\Controllers\PageController@store', 'admin.pages.store');
    $router->get('/pages/edit/{id}', 'GlassPress\App\Admin\Controllers\PageController@edit', 'admin.pages.edit');
    $router->post('/pages/update/{id}', 'GlassPress\App\Admin\Controllers\PageController@update', 'admin.pages.update');
    $router->post('/pages/delete/{id}', 'GlassPress\App\Admin\Controllers\PageController@delete', 'admin.pages.delete');

    // Media
    $router->get('/media', 'GlassPress\App\Admin\Controllers\MediaController@index', 'admin.media');
    $router->get('/media/create', 'GlassPress\App\Admin\Controllers\MediaController@create', 'admin.media.create');
    $router->get('/media/edit/{id}', 'GlassPress\App\Admin\Controllers\MediaController@edit', 'admin.media.edit');
    $router->post('/media/store', 'GlassPress\App\Admin\Controllers\MediaController@store', 'admin.media.store');
    $router->post('/media/upload', 'GlassPress\App\Admin\Controllers\MediaController@upload', 'admin.media.upload');
    $router->post('/media/update/{id}', 'GlassPress\App\Admin\Controllers\MediaController@update', 'admin.media.update');
    $router->post('/media/delete/{id}', 'GlassPress\App\Admin\Controllers\MediaController@delete', 'admin.media.delete');
    $router->get('/media/browse', 'GlassPress\App\Admin\Controllers\MediaController@browse', 'admin.media.browse');

    // Categories
    $router->get('/categories', 'GlassPress\App\Admin\Controllers\TaxonomyController@categories', 'admin.categories');
    $router->get('/categories/edit/{id}', 'GlassPress\App\Admin\Controllers\TaxonomyController@editCategory', 'admin.categories.edit');
    $router->post('/categories/store', 'GlassPress\App\Admin\Controllers\TaxonomyController@storeCategory', 'admin.categories.store');
    $router->post('/categories/update/{id}', 'GlassPress\App\Admin\Controllers\TaxonomyController@updateCategory', 'admin.categories.update');
    $router->post('/categories/delete/{id}', 'GlassPress\App\Admin\Controllers\TaxonomyController@deleteCategory', 'admin.categories.delete');

    // Tags
    $router->get('/tags', 'GlassPress\App\Admin\Controllers\TaxonomyController@tags', 'admin.tags');
    $router->get('/tags/edit/{id}', 'GlassPress\App\Admin\Controllers\TaxonomyController@editTag', 'admin.tags.edit');
    $router->post('/tags/store', 'GlassPress\App\Admin\Controllers\TaxonomyController@storeTag', 'admin.tags.store');
    $router->post('/tags/update/{id}', 'GlassPress\App\Admin\Controllers\TaxonomyController@updateTag', 'admin.tags.update');
    $router->post('/tags/delete/{id}', 'GlassPress\App\Admin\Controllers\TaxonomyController@deleteTag', 'admin.tags.delete');

    // Comments
    $router->get('/comments', 'GlassPress\App\Admin\Controllers\CommentController@index', 'admin.comments');
    $router->get('/comments/edit/{id}', 'GlassPress\App\Admin\Controllers\CommentController@edit', 'admin.comments.edit');
    $router->post('/comments/update/{id}', 'GlassPress\App\Admin\Controllers\CommentController@update', 'admin.comments.update');
    $router->post('/comments/approve/{id}', 'GlassPress\App\Admin\Controllers\CommentController@approve', 'admin.comments.approve');
    $router->post('/comments/unapprove/{id}', 'GlassPress\App\Admin\Controllers\CommentController@unapprove', 'admin.comments.unapprove');
    $router->post('/comments/spam/{id}', 'GlassPress\App\Admin\Controllers\CommentController@spam', 'admin.comments.spam');
    $router->post('/comments/trash/{id}', 'GlassPress\App\Admin\Controllers\CommentController@trash', 'admin.comments.trash');
    $router->post('/comments/delete/{id}', 'GlassPress\App\Admin\Controllers\CommentController@delete', 'admin.comments.delete');
    $router->post('/comments/reply/{id}', 'GlassPress\App\Admin\Controllers\CommentController@reply', 'admin.comments.reply');
    $router->post('/comments/bulk', 'GlassPress\App\Admin\Controllers\CommentController@bulk', 'admin.comments.bulk');

    // Menus
    $router->get('/menus', 'GlassPress\App\Admin\Controllers\MenuController@index', 'admin.menus');
    $router->post('/menus/store', 'GlassPress\App\Admin\Controllers\MenuController@store', 'admin.menus.store');
    $router->post('/menus/update/{id}', 'GlassPress\App\Admin\Controllers\MenuController@update', 'admin.menus.update');
    $router->post('/menus/delete/{id}', 'GlassPress\App\Admin\Controllers\MenuController@delete', 'admin.menus.delete');
    $router->post('/menus/add-item', 'GlassPress\App\Admin\Controllers\MenuController@addItem', 'admin.menus.addItem');

    // Users
    $router->get('/users', 'GlassPress\App\Admin\Controllers\UserController@index', 'admin.users');
    $router->get('/users/create', 'GlassPress\App\Admin\Controllers\UserController@create', 'admin.users.create');
    $router->post('/users/store', 'GlassPress\App\Admin\Controllers\UserController@store', 'admin.users.store');
    $router->get('/users/edit/{id}', 'GlassPress\App\Admin\Controllers\UserController@edit', 'admin.users.edit');
    $router->post('/users/update/{id}', 'GlassPress\App\Admin\Controllers\UserController@update', 'admin.users.update');
    $router->post('/users/delete/{id}', 'GlassPress\App\Admin\Controllers\UserController@delete', 'admin.users.delete');
    $router->get('/profile', 'GlassPress\App\Admin\Controllers\UserController@profile', 'admin.profile');
    $router->post('/profile', 'GlassPress\App\Admin\Controllers\UserController@updateProfile', 'admin.profile.update');

    // Settings
    $router->get('/settings', 'GlassPress\App\Admin\Controllers\SettingsController@general', 'admin.settings');
    $router->get('/settings/general', 'GlassPress\App\Admin\Controllers\SettingsController@general', 'admin.settings.general');
    $router->post('/settings/save', 'GlassPress\App\Admin\Controllers\SettingsController@save', 'admin.settings.save');
    $router->get('/settings/writing', 'GlassPress\App\Admin\Controllers\SettingsController@writing', 'admin.settings.writing');
    $router->get('/settings/reading', 'GlassPress\App\Admin\Controllers\SettingsController@reading', 'admin.settings.reading');
    $router->get('/settings/discussion', 'GlassPress\App\Admin\Controllers\SettingsController@discussion', 'admin.settings.discussion');
    $router->get('/settings/media', 'GlassPress\App\Admin\Controllers\SettingsController@media', 'admin.settings.media');
    $router->get('/settings/permalinks', 'GlassPress\App\Admin\Controllers\SettingsController@permalinks', 'admin.settings.permalinks');
    $router->get('/settings/seo', 'GlassPress\App\Admin\Controllers\SettingsController@seo', 'admin.settings.seo');
    $router->get('/settings/redirects', 'GlassPress\App\Admin\Controllers\SettingsController@redirects', 'admin.settings.redirects');
    $router->post('/settings/redirects/store', 'GlassPress\App\Admin\Controllers\SettingsController@storeRedirect', 'admin.settings.redirects.store');
    $router->post('/settings/redirects/delete/{id}', 'GlassPress\App\Admin\Controllers\SettingsController@deleteRedirect', 'admin.settings.redirects.delete');
    $router->get('/settings/appearance', 'GlassPress\App\Admin\Controllers\SettingsController@appearance', 'admin.settings.appearance');
    $router->get('/settings/advanced', 'GlassPress\App\Admin\Controllers\SettingsController@advanced', 'admin.settings.advanced');

    // Tools
    $router->get('/tools', 'GlassPress\App\Admin\Controllers\ToolsController@index', 'admin.tools');
    $router->get('/tools/seo-health', 'GlassPress\App\Admin\Controllers\ToolsController@seoHealth', 'admin.tools.seo');
    $router->get('/tools/404-log', 'GlassPress\App\Admin\Controllers\ToolsController@log404', 'admin.tools.404');
    $router->post('/tools/clear-cache', 'GlassPress\App\Admin\Controllers\ToolsController@clearCache', 'admin.tools.cache');
    $router->get('/tools/export', 'GlassPress\App\Admin\Controllers\ToolsController@export', 'admin.tools.export');
    $router->post('/tools/import', 'GlassPress\App\Admin\Controllers\ToolsController@import', 'admin.tools.import');
    $router->get('/tools/system-info', 'GlassPress\App\Admin\Controllers\ToolsController@systemInfo', 'admin.tools.system');
});
