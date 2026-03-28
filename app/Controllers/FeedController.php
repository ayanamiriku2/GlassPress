<?php
namespace GlassPress\App\Controllers;

use GlassPress\Core\Application;

class FeedController
{
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function rss(): void
    {
        $db = $this->app->getService('db');
        $settings = $this->app->getService('settings');
        $perPage = (int) $settings->get('posts_per_rss', 10);
        $siteUrl = $this->app->getSiteUrl();
        $siteName = htmlspecialchars($settings->get('site_title', 'GlassPress'), ENT_XML1, 'UTF-8');
        $siteDesc = htmlspecialchars($settings->get('site_tagline', ''), ENT_XML1, 'UTF-8');
        $rssExcerpt = $settings->get('rss_excerpt', 'excerpt');

        $posts = $db->fetchAll(sprintf(
            "SELECT p.*, u.display_name as author_name
             FROM %s p
             LEFT JOIN %s u ON u.id = p.author_id
             WHERE p.post_type = 'post' AND p.status = 'publish'
             ORDER BY p.published_at DESC LIMIT %d",
            $db->prefix('posts'),
            $db->prefix('users'),
            $perPage
        ));

        header('Content-Type: application/rss+xml; charset=UTF-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/">' . "\n";
        echo "<channel>\n";
        echo "  <title>{$siteName}</title>\n";
        echo "  <link>{$siteUrl}</link>\n";
        echo "  <description>{$siteDesc}</description>\n";
        echo "  <language>" . htmlspecialchars($settings->get('language', 'en'), ENT_XML1) . "</language>\n";
        echo "  <lastBuildDate>" . date('r') . "</lastBuildDate>\n";
        echo "  <atom:link href=\"{$siteUrl}/feed\" rel=\"self\" type=\"application/rss+xml\" />\n";

        foreach ($posts as $post) {
            $link = $siteUrl . '/' . $post['slug'];
            $title = htmlspecialchars($post['title'], ENT_XML1, 'UTF-8');
            $author = htmlspecialchars($post['author_name'] ?? 'Unknown', ENT_XML1, 'UTF-8');
            $pubDate = date('r', strtotime($post['published_at'] ?? $post['created_at']));

            if ($rssExcerpt === 'full') {
                $description = htmlspecialchars($post['content'], ENT_XML1, 'UTF-8');
            } else {
                $text = $post['excerpt'] ?: mb_substr(strip_tags($post['content']), 0, 300);
                $description = htmlspecialchars($text, ENT_XML1, 'UTF-8');
            }

            // Get categories
            $cats = $db->fetchAll(sprintf(
                "SELECT t.name FROM %s t JOIN %s pt ON pt.taxonomy_id = t.id WHERE pt.post_id = ? AND t.taxonomy = 'category'",
                $db->prefix('taxonomies'),
                $db->prefix('post_taxonomy')
            ), [$post['id']]);

            echo "  <item>\n";
            echo "    <title>{$title}</title>\n";
            echo "    <link>{$link}</link>\n";
            echo "    <guid isPermaLink=\"true\">{$link}</guid>\n";
            echo "    <pubDate>{$pubDate}</pubDate>\n";
            echo "    <dc:creator>{$author}</dc:creator>\n";
            echo "    <description><![CDATA[{$description}]]></description>\n";
            foreach ($cats as $cat) {
                echo "    <category>" . htmlspecialchars($cat['name'], ENT_XML1) . "</category>\n";
            }
            echo "  </item>\n";
        }

        echo "</channel>\n</rss>";
        exit;
    }

    public function atom(): void
    {
        $db = $this->app->getService('db');
        $settings = $this->app->getService('settings');
        $perPage = (int) $settings->get('posts_per_rss', 10);
        $siteUrl = $this->app->getSiteUrl();
        $siteName = htmlspecialchars($settings->get('site_title', 'GlassPress'), ENT_XML1, 'UTF-8');
        $siteDesc = htmlspecialchars($settings->get('site_tagline', ''), ENT_XML1, 'UTF-8');

        $posts = $db->fetchAll(sprintf(
            "SELECT p.*, u.display_name as author_name
             FROM %s p LEFT JOIN %s u ON u.id = p.author_id
             WHERE p.post_type = 'post' AND p.status = 'publish'
             ORDER BY p.published_at DESC LIMIT %d",
            $db->prefix('posts'),
            $db->prefix('users'),
            $perPage
        ));

        header('Content-Type: application/atom+xml; charset=UTF-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<feed xmlns="http://www.w3.org/2005/Atom">' . "\n";
        echo "  <title>{$siteName}</title>\n";
        echo "  <subtitle>{$siteDesc}</subtitle>\n";
        echo "  <link href=\"{$siteUrl}/feed/atom\" rel=\"self\" />\n";
        echo "  <link href=\"{$siteUrl}\" />\n";
        echo "  <id>{$siteUrl}/</id>\n";
        echo "  <updated>" . date('c') . "</updated>\n";

        foreach ($posts as $post) {
            $link = $siteUrl . '/' . $post['slug'];
            $title = htmlspecialchars($post['title'], ENT_XML1, 'UTF-8');
            $author = htmlspecialchars($post['author_name'] ?? 'Unknown', ENT_XML1, 'UTF-8');
            $published = date('c', strtotime($post['published_at'] ?? $post['created_at']));
            $updated = date('c', strtotime($post['updated_at'] ?? $post['created_at']));
            $summary = htmlspecialchars($post['excerpt'] ?: mb_substr(strip_tags($post['content']), 0, 300), ENT_XML1, 'UTF-8');

            echo "  <entry>\n";
            echo "    <title>{$title}</title>\n";
            echo "    <link href=\"{$link}\" />\n";
            echo "    <id>{$link}</id>\n";
            echo "    <published>{$published}</published>\n";
            echo "    <updated>{$updated}</updated>\n";
            echo "    <author><name>{$author}</name></author>\n";
            echo "    <summary>{$summary}</summary>\n";
            echo "  </entry>\n";
        }

        echo "</feed>";
        exit;
    }
}
