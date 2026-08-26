<?php
declare(strict_types=1);

namespace App\Controllers\Site;

use App\Controllers\Controller;
use App\Core\Lang;
use App\Core\Request;
use App\Core\Url;
use App\Core\View;
use App\Models\Category;
use App\Models\Media;
use App\Models\Product;

final class ProductController extends Controller
{
    private const PER_PAGE = 12;

    public function index(Request $request, array $params = []): never
    {
        $categorySlug = (string) ($params['category'] ?? '');
        $category     = null;

        if ($categorySlug !== '') {
            $category = Category::findBySlug($categorySlug);

            if ($category === null) {
                $this->notFound();
            }
        }

        $filters = [
            'category' => $categorySlug,
            'search'   => (string) ($request->query('q') ?? ''),
            'sort'     => (string) ($request->query('sort') ?? ''),
        ];

        $result = Product::listing($filters, max(1, $request->integer('page', 1)), self::PER_PAGE);

        $crumbs = [['name' => Lang::get('common.home'), 'url' => Url::home()]];
        $crumbs[] = ['name' => Lang::get('nav.products'), 'url' => Url::products()];
        if ($category !== null) {
            $crumbs[] = ['name' => (string) $category['name'], 'url' => Url::category($categorySlug)];
        }

        View::start('schema');
        echo '<script type="application/ld+json">' . $this->breadcrumbSchema($crumbs) . '</script>';
        View::stop();

        $meta = $category !== null
            ? $this->meta($category, 'name', 'description')
            : ['metaTitle' => Lang::get('products.title'), 'metaDescription' => Lang::get('products.lead')];

        $this->view('site.products', $meta + [
            'result'     => $result,
            'categories' => Category::published(),
            'category'   => $category,
            'filters'    => $filters,
            'canonical'  => $this->canonical($request->path()),
        ]);
    }

    public function show(Request $request, array $params): never
    {
        $product = Product::findBySlug((string) ($params['slug'] ?? ''));

        if ($product === null) {
            $this->notFound();
        }

        $related = Product::related(
            (int) $product['id'],
            $product['category_id'] === null ? null : (int) $product['category_id'],
            3
        );

        $crumbs = [
            ['name' => Lang::get('common.home'),   'url' => Url::home()],
            ['name' => Lang::get('nav.products'),  'url' => Url::products()],
        ];
        if (!empty($product['category_slug'])) {
            $crumbs[] = [
                'name' => (string) $product['category_name'],
                'url'  => Url::category((string) $product['category_slug']),
            ];
        }
        $crumbs[] = ['name' => (string) $product['name'], 'url' => Url::product((string) $product['slug'])];

        $image = (string) ($product['image_path'] ?? '');

        // Product structured data. No price or availability is emitted because
        // the source materials define neither — inventing them would be wrong
        // and would also trip Google's structured-data validation.
        $productSchema = array_filter([
            '@context'    => 'https://schema.org',
            '@type'       => 'Product',
            'name'        => $product['name'],
            'description' => excerpt((string) ($product['summary'] ?? $product['description'] ?? ''), 400) ?: null,
            'sku'         => $product['model_code'] ?: null,
            'model'       => $product['model_code'] ?: null,
            'image'       => $image !== '' ? Url::upload($image) : null,
            'url'         => Url::product((string) $product['slug']),
            'category'    => $product['category_name'] ?: null,
            'brand'       => [
                '@type' => 'Brand',
                'name'  => \App\Models\Setting::get('site_name', 'Rahyaft Sanat'),
            ],
            'manufacturer' => [
                '@type' => 'Organization',
                'name'  => \App\Models\Setting::get('site_name', 'Rahyaft Sanat'),
            ],
            'additionalProperty' => $this->specProperties($product['specGroups']) ?: null,
        ]);

        View::start('schema');
        echo '<script type="application/ld+json">'
            . json_encode($productSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            . '</script>';
        echo '<script type="application/ld+json">' . $this->breadcrumbSchema($crumbs) . '</script>';
        View::stop();

        $this->view('site.product', $this->meta($product, 'name', 'summary') + [
            'product'   => $product,
            'related'   => $related,
            'canonical' => $this->canonical($request->path()),
            'ogType'    => 'product',
            'ogImage'   => $image !== '' ? Url::upload($image) : null,
        ]);
    }

    /** Flatten spec groups into schema.org PropertyValue entries. */
    private function specProperties(array $groups): array
    {
        $properties = [];

        foreach ($groups as $group) {
            foreach ($group['rows'] as $row) {
                if ($row['label'] === '' || $row['value'] === '') {
                    continue;
                }

                $properties[] = [
                    '@type' => 'PropertyValue',
                    'name'  => $row['label'],
                    'value' => $row['value'],
                ];
            }
        }

        return array_slice($properties, 0, 50);
    }
}
