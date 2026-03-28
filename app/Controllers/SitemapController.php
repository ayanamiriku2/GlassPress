<?php
namespace GlassPress\App\Controllers;

use GlassPress\Core\Application;

class SitemapController
{
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function index(): void
    {
        $siteUrl = $this->app->getSiteUrl();
        header('Content-Type: application/xml; charset=UTF-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        echo "  <sitemap><loc>{$siteUrl}/sitemap-posts.xml</loc></sitemap>\n";
        echo "  <sitemap><loc>{$siteUrl}/sitemap-pages.xml</loc></sitemap>\n";
        echo "  <sitemap><loc>{$siteUrl}/sitemap-categories.xml</loc></sitemap>\n";
        echo "  <sitemap><loc>{$siteUrl}/sitemap-tags.xml</loc></sitemap>\n";
        echo '</sitemapindex>';
        exit;
    }

    public function posts(): void
    {
        $db = $this->app->getService('db');
        $siteUrl = $this->app->getSiteUrl();

        $posts = $db->fetchAll(sprintf(
            "SELECT slug, updated_at FROM %s WHERE post_type = 'post' AND status = 'publish' ORDER BY published_at DESC",
            $db->prefix('posts')
        ));

        $this->outputUrlSet($posts, function ($post) use ($siteUrl) {
            return [
                'loc' => $siteUrl . '/' . $post['slug'],
                'lastmod' => date('Y-m-d', strtotime($post['updated_at'])),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        });
    }

    public function pages(): void
    {
        $db = $this->app->getService('db');
        $siteUrl = $this->app->getSiteUrl();

        $pages = $db->fetchAll(sprintf(
            "SELECT slug, updated_at FROM %s WHERE post_type = 'page' AND status = 'publish' ORDER BY menu_order ASC",
            $db->prefix('posts')
        ));

        $entries = [['loc' => $siteUrl . '/', 'lastmod' => date('Y-m-d'), 'changefreq' => 'daily', 'priority' => '1.0']];
        foreach ($pages as $page) {
            $entries[] = [
                'loc' => $siteUrl . '/' . $page['slug'],
                'lastmod' => date('Y-m-d', strtotime($page['updated_at'])),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ];
        }

        $this->outputXml($entries);
    }

    public function categories(): void
    {
        $db = $this->app->getService('db');
        $settings = $this->app->getService('settings');
        $siteUrl = $this->app->getSiteUrl();
        $base = $settings->get('category_base', 'category');

        $cats = $db->fetchAll(sprintf(
            "SELECT slug FROM %s WHERE taxonomy = 'category' AND count > 0",
            $db->prefix('taxonomies')
        ));

        $this->outputUrlSet($cats, function ($cat) use ($siteUrl, $base) {
            return [
                'loc' => $siteUrl . '/' . $base . '/' . $cat['slug'],
                'changefreq' => 'weekly',
                'priority' => '0.5',
            ];
        });
    }

    public function tags(): void
    {
        $db = $this->app->getService('db');
        $settings = $this->app->getService('settings');
        $siteUrl = $this->app->getSiteUrl();
        $base = $settings->get('tag_base', 'tag');

        if ($settings->get('seo_noindex_tags', '0') === '1') {
            header('Content-Type: application/xml; charset=UTF-8');
            echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>';
            exit;
        }

        $tags = $db->fetchAll(sprintf(
            "SELECT slug FROM %s WHERE taxonomy = 'tag' AND count > 0",
            $db->prefix('taxonomies')
        ));

        $this->outputUrlSet($tags, function ($tag) use ($siteUrl, $base) {
            return [
                'loc' => $siteUrl . '/' . $base . '/' . $tag['slug'],
                'changefreq' => 'weekly',
                'priority' => '0.3',
            ];
        });
    }

    private function outputUrlSet(array $items, callable $mapper): void
    {
        $entries = array_map($mapper, $items);
        $this->outputXml($entries);
    }

    private function outputXml(array $entries): void
    {
        header('Content-Type: application/xml; charset=UTF-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($entries as $entry) {
            echo "  <url>\n";
            echo "    <loc>" . htmlspecialchars($entry['loc'], ENT_XML1) . "</loc>\n";
            if (isset($entry['lastmod'])) {
                echo "    <lastmod>{$entry['lastmod']}</lastmod>\n";
            }
            if (isset($entry['changefreq'])) {
                echo "    <changefreq>{$entry['changefreq']}</changefreq>\n";
            }
            if (isset($entry['priority'])) {
                echo "    <priority>{$entry['priority']}</priority>\n";
            }
            echo "  </url>\n";
        }
        echo '</urlset>';
        exit;
    }
}
