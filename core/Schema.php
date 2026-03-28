<?php
namespace GlassPress\Core;

/**
 * Database schema definition and migration.
 */
class Schema
{
    /**
     * Get all table creation SQL statements.
     */
    public static function getTables(string $prefix): array
    {
        $charset = 'utf8mb4';
        $collate = 'utf8mb4_unicode_ci';

        return [
            // Users
            "{$prefix}users" => "CREATE TABLE IF NOT EXISTS `{$prefix}users` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `username` VARCHAR(60) NOT NULL,
                `email` VARCHAR(100) NOT NULL,
                `password` VARCHAR(255) NOT NULL,
                `display_name` VARCHAR(100) NOT NULL DEFAULT '',
                `first_name` VARCHAR(60) NOT NULL DEFAULT '',
                `last_name` VARCHAR(60) NOT NULL DEFAULT '',
                `bio` TEXT,
                `avatar` VARCHAR(255) DEFAULT NULL,
                `role` VARCHAR(30) NOT NULL DEFAULT 'subscriber',
                `status` ENUM('active','inactive','banned') NOT NULL DEFAULT 'active',
                `last_login` DATETIME DEFAULT NULL,
                `login_ip` VARCHAR(45) DEFAULT NULL,
                `reset_token` VARCHAR(255) DEFAULT NULL,
                `reset_token_expires` DATETIME DEFAULT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `idx_username` (`username`),
                UNIQUE KEY `idx_email` (`email`),
                KEY `idx_role` (`role`),
                KEY `idx_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collate}",

            // Posts (unified for posts and pages)
            "{$prefix}posts" => "CREATE TABLE IF NOT EXISTS `{$prefix}posts` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `author_id` BIGINT UNSIGNED NOT NULL,
                `title` VARCHAR(255) NOT NULL DEFAULT '',
                `slug` VARCHAR(255) NOT NULL DEFAULT '',
                `content` LONGTEXT,
                `excerpt` TEXT,
                `status` ENUM('draft','publish','scheduled','pending','private','trash') NOT NULL DEFAULT 'draft',
                `post_type` ENUM('post','page') NOT NULL DEFAULT 'post',
                `comment_status` ENUM('open','closed') NOT NULL DEFAULT 'open',
                `featured_image_id` BIGINT UNSIGNED DEFAULT NULL,
                `parent_id` BIGINT UNSIGNED DEFAULT NULL,
                `menu_order` INT NOT NULL DEFAULT 0,
                `page_template` VARCHAR(100) DEFAULT NULL,
                `is_sticky` TINYINT(1) NOT NULL DEFAULT 0,
                `password` VARCHAR(100) DEFAULT NULL,
                `published_at` DATETIME DEFAULT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `idx_slug_type` (`slug`, `post_type`),
                KEY `idx_author` (`author_id`),
                KEY `idx_status_type` (`status`, `post_type`),
                KEY `idx_parent` (`parent_id`),
                KEY `idx_published` (`published_at`),
                KEY `idx_sticky` (`is_sticky`),
                KEY `idx_type_status_date` (`post_type`, `status`, `published_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collate}",

            // Post Revisions
            "{$prefix}post_revisions" => "CREATE TABLE IF NOT EXISTS `{$prefix}post_revisions` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `post_id` BIGINT UNSIGNED NOT NULL,
                `author_id` BIGINT UNSIGNED NOT NULL,
                `title` VARCHAR(255) NOT NULL DEFAULT '',
                `content` LONGTEXT,
                `excerpt` TEXT,
                `revision_type` ENUM('manual','autosave') NOT NULL DEFAULT 'manual',
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_post` (`post_id`),
                KEY `idx_created` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collate}",

            // Taxonomies
            "{$prefix}taxonomies" => "CREATE TABLE IF NOT EXISTS `{$prefix}taxonomies` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(200) NOT NULL,
                `slug` VARCHAR(200) NOT NULL,
                `taxonomy` VARCHAR(50) NOT NULL DEFAULT 'category',
                `description` TEXT,
                `parent_id` BIGINT UNSIGNED DEFAULT NULL,
                `count` INT UNSIGNED NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                UNIQUE KEY `idx_slug_tax` (`slug`, `taxonomy`),
                KEY `idx_taxonomy` (`taxonomy`),
                KEY `idx_parent` (`parent_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collate}",

            // Post-Taxonomy relationships
            "{$prefix}post_taxonomy" => "CREATE TABLE IF NOT EXISTS `{$prefix}post_taxonomy` (
                `post_id` BIGINT UNSIGNED NOT NULL,
                `taxonomy_id` BIGINT UNSIGNED NOT NULL,
                PRIMARY KEY (`post_id`, `taxonomy_id`),
                KEY `idx_taxonomy` (`taxonomy_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collate}",

            // Comments
            "{$prefix}comments" => "CREATE TABLE IF NOT EXISTS `{$prefix}comments` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `post_id` BIGINT UNSIGNED NOT NULL,
                `parent_id` BIGINT UNSIGNED DEFAULT NULL,
                `user_id` BIGINT UNSIGNED DEFAULT NULL,
                `author_name` VARCHAR(100) NOT NULL DEFAULT '',
                `author_email` VARCHAR(100) NOT NULL DEFAULT '',
                `author_url` VARCHAR(255) DEFAULT NULL,
                `author_ip` VARCHAR(45) DEFAULT NULL,
                `content` TEXT NOT NULL,
                `status` ENUM('approved','pending','spam','trash') NOT NULL DEFAULT 'pending',
                `user_agent` VARCHAR(255) DEFAULT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_post` (`post_id`),
                KEY `idx_parent` (`parent_id`),
                KEY `idx_status` (`status`),
                KEY `idx_post_status` (`post_id`, `status`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collate}",

            // Media
            "{$prefix}media" => "CREATE TABLE IF NOT EXISTS `{$prefix}media` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` BIGINT UNSIGNED NOT NULL,
                `filename` VARCHAR(255) NOT NULL,
                `original_name` VARCHAR(255) NOT NULL,
                `mime_type` VARCHAR(100) NOT NULL,
                `file_size` BIGINT UNSIGNED NOT NULL DEFAULT 0,
                `file_path` VARCHAR(500) NOT NULL,
                `width` INT UNSIGNED NOT NULL DEFAULT 0,
                `height` INT UNSIGNED NOT NULL DEFAULT 0,
                `alt_text` VARCHAR(255) NOT NULL DEFAULT '',
                `caption` TEXT,
                `description` TEXT,
                `sizes` JSON,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_user` (`user_id`),
                KEY `idx_mime` (`mime_type`),
                KEY `idx_created` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collate}",

            // Menus
            "{$prefix}menus" => "CREATE TABLE IF NOT EXISTS `{$prefix}menus` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(100) NOT NULL,
                `slug` VARCHAR(100) NOT NULL,
                `location` VARCHAR(50) DEFAULT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `idx_slug` (`slug`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collate}",

            // Menu Items
            "{$prefix}menu_items" => "CREATE TABLE IF NOT EXISTS `{$prefix}menu_items` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `menu_id` BIGINT UNSIGNED NOT NULL,
                `parent_id` BIGINT UNSIGNED DEFAULT NULL,
                `title` VARCHAR(255) NOT NULL,
                `url` VARCHAR(500) NOT NULL DEFAULT '',
                `target` VARCHAR(20) NOT NULL DEFAULT '_self',
                `item_type` ENUM('custom','post','page','category','tag') NOT NULL DEFAULT 'custom',
                `item_id` BIGINT UNSIGNED DEFAULT NULL,
                `css_class` VARCHAR(100) DEFAULT NULL,
                `sort_order` INT NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                KEY `idx_menu` (`menu_id`),
                KEY `idx_parent` (`parent_id`),
                KEY `idx_sort_order` (`sort_order`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collate}",

            // Settings
            "{$prefix}settings" => "CREATE TABLE IF NOT EXISTS `{$prefix}settings` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `setting_key` VARCHAR(100) NOT NULL,
                `setting_value` LONGTEXT,
                `autoload` TINYINT(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id`),
                UNIQUE KEY `idx_key` (`setting_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collate}",

            // SEO Meta
            "{$prefix}seo_meta" => "CREATE TABLE IF NOT EXISTS `{$prefix}seo_meta` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `object_type` ENUM('post','page','category','tag','author') NOT NULL,
                `object_id` BIGINT UNSIGNED NOT NULL,
                `seo_title` VARCHAR(255) DEFAULT NULL,
                `seo_description` VARCHAR(320) DEFAULT NULL,
                `canonical_url` VARCHAR(500) DEFAULT NULL,
                `robots` VARCHAR(50) DEFAULT NULL,
                `og_title` VARCHAR(255) DEFAULT NULL,
                `og_description` VARCHAR(320) DEFAULT NULL,
                `og_image` VARCHAR(500) DEFAULT NULL,
                `twitter_title` VARCHAR(255) DEFAULT NULL,
                `twitter_description` VARCHAR(320) DEFAULT NULL,
                `twitter_image` VARCHAR(500) DEFAULT NULL,
                `schema_type` VARCHAR(50) DEFAULT NULL,
                `focus_keyword` VARCHAR(100) DEFAULT NULL,
                `breadcrumb_title` VARCHAR(100) DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `idx_object` (`object_type`, `object_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collate}",

            // Redirects
            "{$prefix}redirects" => "CREATE TABLE IF NOT EXISTS `{$prefix}redirects` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `source_url` VARCHAR(500) NOT NULL,
                `target_url` VARCHAR(500) NOT NULL,
                `status_code` SMALLINT NOT NULL DEFAULT 301,
                `hit_count` INT UNSIGNED NOT NULL DEFAULT 0,
                `last_hit` DATETIME DEFAULT NULL,
                `is_regex` TINYINT(1) NOT NULL DEFAULT 0,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_source` (`source_url`(191)),
                KEY `idx_active` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collate}",

            // 404 Log
            "{$prefix}log_404" => "CREATE TABLE IF NOT EXISTS `{$prefix}log_404` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `url` VARCHAR(500) NOT NULL,
                `referrer` VARCHAR(500) DEFAULT NULL,
                `user_agent` VARCHAR(255) DEFAULT NULL,
                `ip_address` VARCHAR(45) DEFAULT NULL,
                `hit_count` INT UNSIGNED NOT NULL DEFAULT 1,
                `first_hit` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `last_hit` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `idx_url` (`url`(191)),
                KEY `idx_hits` (`hit_count`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collate}",

            // Audit Log
            "{$prefix}audit_log" => "CREATE TABLE IF NOT EXISTS `{$prefix}audit_log` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `action` VARCHAR(50) NOT NULL,
                `target_type` VARCHAR(50) NOT NULL DEFAULT '',
                `target_id` BIGINT UNSIGNED NOT NULL DEFAULT 0,
                `user_id` BIGINT UNSIGNED NOT NULL DEFAULT 0,
                `ip_address` VARCHAR(45) DEFAULT NULL,
                `user_agent` VARCHAR(255) DEFAULT NULL,
                `details` JSON,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_action` (`action`),
                KEY `idx_user` (`user_id`),
                KEY `idx_created` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collate}",
        ];
    }

    /**
     * Get default settings to seed.
     */
    public static function getDefaultSettings(): array
    {
        return [
            'site_title' => 'My GlassPress Site',
            'site_tagline' => 'A Modern CMS',
            'site_url' => '',
            'admin_email' => '',
            'timezone' => 'UTC',
            'date_format' => 'F j, Y',
            'time_format' => 'g:i a',
            'language' => 'en',
            'posts_per_page' => '10',
            'posts_per_rss' => '10',
            'rss_excerpt' => 'excerpt',
            'default_post_status' => 'draft',
            'default_comment_status' => 'open',
            'comments_per_page' => '20',
            'comment_moderation' => '1',
            'comment_registration' => '0',
            'show_avatars' => '1',
            'permalink_structure' => '/{slug}',
            'category_base' => 'category',
            'tag_base' => 'tag',
            'upload_max_size' => '10485760',
            'thumbnail_width' => '150',
            'thumbnail_height' => '150',
            'medium_width' => '300',
            'medium_height' => '300',
            'large_width' => '1024',
            'large_height' => '1024',
            'active_theme' => 'flavor',
            'homepage_type' => 'posts',
            'homepage_id' => '0',
            'blog_page_id' => '0',
            'seo_title_separator' => '|',
            'seo_homepage_title' => '',
            'seo_homepage_description' => '',
            'seo_noindex_search' => '1',
            'seo_noindex_archives' => '0',
            'seo_noindex_tags' => '0',
            'seo_noindex_author' => '1',
            'seo_enable_sitemap' => '1',
            'seo_enable_schema' => '1',
            'seo_organization_name' => '',
            'seo_organization_logo' => '',
            'seo_social_facebook' => '',
            'seo_social_twitter' => '',
            'seo_social_default_image' => '',
            'robots_txt' => "User-agent: *\nAllow: /\n\nDisallow: /admin/\nDisallow: /storage/\nDisallow: /config/\n",
            'custom_css' => '',
            'maintenance_mode' => '0',
            'maintenance_message' => 'We are currently performing maintenance. Please check back soon.',
            'cache_enabled' => '0',
            'cache_ttl' => '3600',
            'trailing_slash' => '0',
        ];
    }

    /**
     * Get sample content for initial installation.
     */
    public static function getSampleContent(): array
    {
        return [
            'posts' => [
                [
                    'title' => 'Welcome to GlassPress',
                    'slug' => 'welcome-to-glasspress',
                    'content' => '<p>Welcome to GlassPress! This is your first post. Edit or delete it, then start writing!</p>
<p>GlassPress is a modern, lightweight CMS built with PHP. It\'s designed to be fast, SEO-friendly, and easy to use on shared hosting environments.</p>
<h2>Getting Started</h2>
<p>Here are a few things you can do to get started:</p>
<ul>
<li>Visit the <a href="/admin">Admin Dashboard</a> to manage your site</li>
<li>Create new posts and pages</li>
<li>Customize your site\'s appearance in Settings</li>
<li>Configure your SEO settings</li>
</ul>
<p>Happy publishing!</p>',
                    'excerpt' => 'Welcome to GlassPress! This is your first post. Edit or delete it, then start writing!',
                    'status' => 'publish',
                    'post_type' => 'post',
                ],
            ],
            'pages' => [
                [
                    'title' => 'About',
                    'slug' => 'about',
                    'content' => '<p>This is an example page. It\'s different from a blog post because it stays in one place and will show up in your site navigation.</p>
<p>You can edit this page in the admin panel, or create new pages for your site.</p>',
                    'excerpt' => '',
                    'status' => 'publish',
                    'post_type' => 'page',
                ],
                [
                    'title' => 'Contact',
                    'slug' => 'contact',
                    'content' => '<p>You can reach us using the information below.</p>
<p>Feel free to edit this page with your actual contact information.</p>',
                    'excerpt' => '',
                    'status' => 'publish',
                    'post_type' => 'page',
                ],
            ],
            'categories' => [
                ['name' => 'Uncategorized', 'slug' => 'uncategorized', 'description' => 'Default category'],
                ['name' => 'News', 'slug' => 'news', 'description' => 'Latest news and updates'],
            ],
            'tags' => [
                ['name' => 'Getting Started', 'slug' => 'getting-started'],
                ['name' => 'Welcome', 'slug' => 'welcome'],
            ],
        ];
    }
}
