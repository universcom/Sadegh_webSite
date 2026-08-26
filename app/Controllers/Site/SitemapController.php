<?php
declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Lang;
use App\Core\Request;
use App\Core\Response;
use App\Core\Url;
use App\Models\Category;
use App\Models\Product;
use App\Models\ResearchProject;

/**
 * XML sitemap and robots.txt.
 *
 * Every URL is emitted once per language with reciprocal xhtml:link alternates,
 * which is what Google expects for a prefix-based multilingual site.
 */
final class SitemapController
{
    public function sitemap(Request $request): never
    {
        $locales = Lang::enabled();
        $entries = [];

        $add = function (string $path, ?string $updatedAt, string $priority, string $frequency) use (&$entries, $locales): void {
            $entries[] = [
                'path'      => $path,
                'lastmod'   => $updatedAt !== null ? date('Y-m-d', strtotime($updatedAt)) : null,
                'priority'  => $priority,
                'frequency' => $frequency,
            ];
        };

        $add('', null, '1.0', 'weekly');
        $add('products', null, '0.9', 'weekly');
        $add('research', null, '0.8', 'monthly');
        $add('about', null, '0.6', 'yearly');
        $add('contact', null, '0.6', 'yearly');

        foreach (Category::published(Lang::default()) as $category) {
            $add('products/category/' . $category['slug'], null, '0.7', 'weekly');
        }

        foreach (Product::sitemapEntries() as $product) {
            $add('products/' . $product['slug'], (string) $product['updated_at'], '0.8', 'monthly');
        }

        foreach (ResearchProject::sitemapEntries() as $project) {
            $add('research/' . $project['slug'], (string) $project['updated_at'], '0.7', 'monthly');
        }

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" '
              . 'xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";

        foreach ($entries as $entry) {
            foreach ($locales as $locale) {
                $xml .= "  <url>\n";
                $xml .= '    <loc>' . htmlspecialchars(Url::lang($entry['path'], $locale), ENT_XML1) . "</loc>\n";

                foreach ($locales as $alternate) {
                    $xml .= sprintf(
                        '    <xhtml:link rel="alternate" hreflang="%s" href="%s"/>' . "\n",
                        htmlspecialchars(Lang::htmlLang($alternate), ENT_XML1),
                        htmlspecialchars(Url::lang($entry['path'], $alternate), ENT_XML1)
                    );
                }

                $xml .= sprintf(
                    '    <xhtml:link rel="alternate" hreflang="x-default" href="%s"/>' . "\n",
                    htmlspecialchars(Url::lang($entry['path'], Lang::default()), ENT_XML1)
                );

                if ($entry['lastmod'] !== null) {
                    $xml .= '    <lastmod>' . $entry['lastmod'] . "</lastmod>\n";
                }

                $xml .= '    <changefreq>' . $entry['frequency'] . "</changefreq>\n";
                $xml .= '    <priority>' . $entry['priority'] . "</priority>\n";
                $xml .= "  </url>\n";
            }
        }

        $xml .= '</urlset>';

        Response::xml($xml);
    }

    public function robots(Request $request): never
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            '',
            '# Administration and internals are never indexed.',
            'Disallow: /admin',
            'Disallow: /install.php',
            'Disallow: /uploads/files/',
            '',
            'Sitemap: ' . Url::to('sitemap.xml'),
        ];

        Response::text(implode("\n", $lines) . "\n");
    }
}
