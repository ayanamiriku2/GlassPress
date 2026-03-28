<?php
/**
 * GlassPress Web Routes
 * 
 * @var \GlassPress\Core\Router $router
 * @var \GlassPress\Core\Application $app
 */

use GlassPress\App\Controllers\FrontendController;

// Sitemap
$router->get('/sitemap.xml', 'GlassPress\App\Controllers\SitemapController@index', 'sitemap');
$router->get('/sitemap-posts.xml', 'GlassPress\App\Controllers\SitemapController@posts', 'sitemap.posts');
$router->get('/sitemap-pages.xml', 'GlassPress\App\Controllers\SitemapController@pages', 'sitemap.pages');
$router->get('/sitemap-categories.xml', 'GlassPress\App\Controllers\SitemapController@categories', 'sitemap.categories');
$router->get('/sitemap-tags.xml', 'GlassPress\App\Controllers\SitemapController@tags', 'sitemap.tags');

// Robots.txt
$router->get('/robots.txt', 'GlassPress\App\Controllers\SeoController@robotsTxt', 'robots');

// RSS Feed
$router->get('/feed', 'GlassPress\App\Controllers\FeedController@rss', 'feed.rss');
$router->get('/feed/atom', 'GlassPress\App\Controllers\FeedController@atom', 'feed.atom');

// Search
$router->get('/search', 'GlassPress\App\Controllers\FrontendController@search', 'search');

// Comment submission
$router->post('/comment', 'GlassPress\App\Controllers\CommentController@store', 'comment.store');
