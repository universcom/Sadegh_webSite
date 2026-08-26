<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Lang;
use App\Core\Response;
use App\Core\Url;
use App\Core\View;
use App\Models\Setting;

abstract class Controller
{
    /** Render a public page inside the site layout and send it. */
    protected function view(string $template, array $data = [], int $status = 200): never
    {
        $html = View::renderWithLayout($template, 'layouts.site', $data);

        Response::html($html, $status);
    }

    /** Render the designed 404 page. */
    protected function notFound(): never
    {
        $this->view('site.error', [
            'status'     => 404,
            'metaTitle'  => Lang::get('error.404.title'),
            'metaRobots' => 'noindex, follow',
            'errorTitle' => Lang::get('error.404.title'),
            'errorBody'  => Lang::get('error.404.body'),
        ], 404);
    }

    /**
     * Build the meta title/description for a page, preferring explicit SEO
     * fields, then the page title, then the site defaults.
     */
    protected function meta(array $source, string $titleKey = 'title', string $descriptionKey = 'summary'): array
    {
        $title = trim((string) ($source['seo_title'] ?? ''));
        if ($title === '') {
            $title = trim((string) ($source[$titleKey] ?? ''));
        }

        $description = trim((string) ($source['seo_description'] ?? ''));
        if ($description === '') {
            $description = trim((string) ($source[$descriptionKey] ?? ''));
        }
        if ($description === '') {
            $description = Setting::get('seo_description', Setting::get('site_tagline', ''));
        }

        return [
            'metaTitle'       => $title !== '' ? $title : Setting::get('site_name', 'Rahyaft Sanat'),
            'metaDescription' => excerpt($description, 300),
        ];
    }

    /** Absolute canonical URL for the current request. */
    protected function canonical(string $path): string
    {
        return Url::to(ltrim($path, '/'));
    }

    /** Breadcrumb structured data for a list of [name, url] pairs. */
    protected function breadcrumbSchema(array $crumbs): string
    {
        $items = [];

        foreach (array_values($crumbs) as $index => $crumb) {
            $items[] = array_filter([
                '@type'    => 'ListItem',
                'position' => $index + 1,
                'name'     => $crumb['name'],
                'item'     => $crumb['url'] ?? null,
            ]);
        }

        return (string) json_encode([
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $items,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
