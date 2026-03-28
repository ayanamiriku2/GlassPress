<?php
namespace GlassPress\App\Controllers;

use GlassPress\Core\Application;

class SeoController
{
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function robotsTxt(): void
    {
        $siteUrl = $this->app->getSiteUrl();
        header('Content-Type: text/plain');

        echo "User-agent: *\n";
        echo "Allow: /\n";
        echo "Disallow: /admin/\n";
        echo "Disallow: /storage/\n";
        echo "Disallow: /config/\n";
        echo "Disallow: /core/\n\n";
        echo "Sitemap: {$siteUrl}/sitemap.xml\n";
        exit;
    }
}
