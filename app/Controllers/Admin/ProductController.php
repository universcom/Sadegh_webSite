<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Lang;
use App\Core\Request;
use App\Core\Response;
use App\Core\Uploader;
use App\Core\Validator;
use App\Models\Category;
use App\Models\Media;
use App\Models\Model;
use App\Models\Product;

final class ProductController extends AdminController
{
    private const TRANSLATED_FIELDS = ['name', 'summary', 'description', 'applications', 'advantages'];

    public function index(Request $request): never
    {
        $filters = [
            'status'   => (string) ($request->query('status') ?? ''),
            'category' => (string) ($request->query('category') ?? ''),
            'search'   => (string) ($request->query('q') ?? ''),
        ];

        $result = Product::adminListing($filters, max(1, $request->integer('page', 1)), 20);

        $this->view('admin.products.index', [
            'pageTitle'  => 'Products',
            'activeNav'  => 'products',
            'result'     => $result,
            'filters'    => $filters,
            'categories' => Category::allForAdmin(),
            'counts'     => [
                'published' => Product::count('published'),
                'draft'     => Product::count('draft'),
                'archived'  => Product::count('archived'),
                'all'       => Product::count(null),
            ],
        ]);
    }

    public function create(Request $request): never
    {
        $this->view('admin.products.form', [
            'pageTitle'  => 'New product',
            'activeNav'  => 'products',
            'product'    => null,
            'categories' => Category::allForAdmin(),
            'errors'     => [],
        ]);
    }

    public function store(Request $request): never
    {
        $this->requireCsrf($request, $this->adminUrl('products/create'));

        $translations = $this->translationInput($request, self::TRANSLATED_FIELDS);
        $errors       = $this->validate($request, $translations);

        if ($errors !== []) {
            $this->view('admin.products.form', [
                'pageTitle'  => 'New product',
                'activeNav'  => 'products',
                'product'    => null,
                'categories' => Category::allForAdmin(),
                'errors'     => $errors,
                'old'        => $request->all(),
            ], 422);
        }

        $slug = $this->resolveSlug($request, $translations, null);
        $id   = Product::create($this->attributes($request, $slug), $translations);

        $this->syncSpecs($id, $request);
        $this->syncFeatures($id, $request);

        $this->back($this->adminUrl('products/' . $id), 'success', 'Product created. You can now add images and documents.');
    }

    public function edit(Request $request, array $params): never
    {
        $product = Product::findForAdmin((int) $params['id']);

        if ($product === null) {
            $this->back($this->adminUrl('products'), 'error', 'That product no longer exists.');
        }

        $this->view('admin.products.form', [
            'pageTitle'  => 'Edit product',
            'activeNav'  => 'products',
            'product'    => $product,
            'categories' => Category::allForAdmin(),
            'errors'     => [],
        ]);
    }

    public function update(Request $request, array $params): never
    {
        $id  = (int) $params['id'];
        $url = $this->adminUrl('products/' . $id);

        $this->requireCsrf($request, $url);

        $product = Product::findForAdmin($id);
        if ($product === null) {
            $this->back($this->adminUrl('products'), 'error', 'That product no longer exists.');
        }

        $translations = $this->translationInput($request, self::TRANSLATED_FIELDS);
        $errors       = $this->validate($request, $translations, $id);

        if ($errors !== []) {
            $this->view('admin.products.form', [
                'pageTitle'  => 'Edit product',
                'activeNav'  => 'products',
                'product'    => $product,
                'categories' => Category::allForAdmin(),
                'errors'     => $errors,
                'old'        => $request->all(),
            ], 422);
        }

        $slug = $this->resolveSlug($request, $translations, $id);
        Product::update($id, $this->attributes($request, $slug), $translations);

        $this->syncSpecs($id, $request);
        $this->syncFeatures($id, $request);

        $this->back($url, 'success', 'Product saved.');
    }

    public function destroy(Request $request, array $params): never
    {
        $id = (int) $params['id'];
        $this->requireCsrf($request, $this->adminUrl('products'));
        $this->requireAdmin($this->adminUrl('products'));

        Product::delete($id);

        $this->back($this->adminUrl('products'), 'success', 'Product deleted.');
    }

    public function status(Request $request, array $params): never
    {
        $id  = (int) $params['id'];
        $url = $this->adminUrl('products');

        $this->requireCsrf($request, $url);

        $status = (string) $request->input('status', '');
        Product::setStatus($id, $status);

        $this->back($url, 'success', 'Product status updated to ' . $status . '.');
    }

    // --- Images -------------------------------------------------------------

    public function uploadImage(Request $request, array $params): never
    {
        $id  = (int) $params['id'];
        $url = $this->adminUrl('products/' . $id);

        $this->requireCsrf($request, $url);

        $file = $request->file('image');

        if (!Uploader::wasUploaded($file)) {
            $this->back($url, 'error', 'Please choose an image to upload.');
        }

        try {
            $mediaId = Media::storeImage($file, [
                'fa' => (string) $request->input('alt_fa', '') ?: null,
                'en' => (string) $request->input('alt_en', '') ?: null,
                'ar' => (string) $request->input('alt_ar', '') ?: null,
            ]);
        } catch (\Throwable $e) {
            $this->back($url, 'error', $e->getMessage());
        }

        $db   = Database::instance();
        $next = (int) $db->value(
            'SELECT COALESCE(MAX(sort_order), -1) + 1 FROM product_images WHERE product_id = :id',
            ['id' => $id]
        );

        $db->insert('product_images', [
            'product_id' => $id,
            'media_id'   => $mediaId,
            'sort_order' => $next,
        ]);

        // The first image uploaded also becomes the cover.
        $hasCover = $db->value('SELECT cover_image_id FROM products WHERE id = :id', ['id' => $id]);
        if ($hasCover === null) {
            $db->update('products', ['cover_image_id' => $mediaId], 'id = :id', ['id' => $id]);
        }

        $this->back($url, 'success', 'Image added to the gallery.');
    }

    public function deleteImage(Request $request, array $params): never
    {
        $id  = (int) $params['id'];
        $url = $this->adminUrl('products/' . $id);

        $this->requireCsrf($request, $url);

        $db      = Database::instance();
        $mediaId = (int) $params['imageId'];

        $db->delete('product_images', 'product_id = :p AND media_id = :m', ['p' => $id, 'm' => $mediaId]);

        // Promote the next remaining image if the cover was removed.
        $cover = $db->value('SELECT cover_image_id FROM products WHERE id = :id', ['id' => $id]);
        if ((int) $cover === $mediaId) {
            $replacement = $db->value(
                'SELECT media_id FROM product_images WHERE product_id = :id ORDER BY sort_order ASC LIMIT 1',
                ['id' => $id]
            );
            $db->update('products', ['cover_image_id' => $replacement], 'id = :id', ['id' => $id]);
        }

        $this->back($url, 'success', 'Image removed from this product.');
    }

    // --- Downloads ----------------------------------------------------------

    public function uploadDownload(Request $request, array $params): never
    {
        $id  = (int) $params['id'];
        $url = $this->adminUrl('products/' . $id);

        $this->requireCsrf($request, $url);

        $file = $request->file('document');

        if (!Uploader::wasUploaded($file)) {
            $this->back($url, 'error', 'Please choose a file to upload.');
        }

        try {
            $mediaId = Media::storeDocument($file);
        } catch (\Throwable $e) {
            $this->back($url, 'error', $e->getMessage());
        }

        $db   = Database::instance();
        $next = (int) $db->value(
            'SELECT COALESCE(MAX(sort_order), -1) + 1 FROM product_downloads WHERE product_id = :id',
            ['id' => $id]
        );

        $db->insert('product_downloads', [
            'product_id' => $id,
            'media_id'   => $mediaId,
            'title_fa'   => (string) $request->input('title_fa', '') ?: null,
            'title_en'   => (string) $request->input('title_en', '') ?: null,
            'title_ar'   => (string) $request->input('title_ar', '') ?: null,
            'sort_order' => $next,
        ]);

        $this->back($url, 'success', 'Document added.');
    }

    public function deleteDownload(Request $request, array $params): never
    {
        $id  = (int) $params['id'];
        $url = $this->adminUrl('products/' . $id);

        $this->requireCsrf($request, $url);

        Database::instance()->delete(
            'product_downloads',
            'id = :d AND product_id = :p',
            ['d' => (int) $params['downloadId'], 'p' => $id]
        );

        $this->back($url, 'success', 'Document removed.');
    }

    // --- Internals ----------------------------------------------------------

    private function validate(Request $request, array $translations, ?int $ignoreId = null): array
    {
        $errors  = [];
        $default = Lang::default();

        if (trim($translations[$default]['name'] ?? '') === '') {
            $errors['name'] = sprintf('The product name is required in %s.', strtoupper($default));
        }

        $slug = trim((string) $request->input('slug', ''));

        if ($slug !== '') {
            $validator = Validator::make(['slug' => $slug], ['slug' => 'slug|max:190'], ['slug' => 'slug']);

            if ($validator->fails()) {
                $errors['slug'] = (string) $validator->first();
            }
        }

        return $errors;
    }

    private function resolveSlug(Request $request, array $translations, ?int $ignoreId): string
    {
        $slug = trim((string) $request->input('slug', ''));

        if ($slug === '') {
            // Prefer the English name for the slug: it produces a readable ASCII
            // URL, where a Persian name would only yield a hash suffix.
            $source = $translations['en']['name'] ?? '';
            if (trim($source) === '') {
                $source = $translations[Lang::default()]['name'] ?? 'product';
            }
            $slug = slugify($source, 'product');
        }

        return Model::uniqueSlug('products', $slug, $ignoreId);
    }

    private function attributes(Request $request, string $slug): array
    {
        $categoryId = $request->integer('category_id', 0);
        $status     = (string) $request->input('status', 'published');

        return [
            'slug'         => $slug,
            'category_id'  => $categoryId > 0 ? $categoryId : null,
            'model_code'   => (string) $request->input('model_code', '') ?: null,
            'is_featured'  => $request->boolean('is_featured') ? 1 : 0,
            'status'       => in_array($status, ['published', 'draft', 'archived'], true) ? $status : 'draft',
            'sort_order'   => $request->integer('sort_order', 0),
            'needs_review' => $request->boolean('needs_review') ? 1 : 0,
        ];
    }

    /**
     * Rewrite the specification tables from the submitted repeatable rows.
     * Groups arrive as specs[<i>][title][<lang>] and specs[<i>][rows][<j>][<lang>][label|value].
     */
    private function syncSpecs(int $productId, Request $request): void
    {
        $groups = $request->raw('specs', []);

        if (!is_array($groups)) {
            return;
        }

        $db = Database::instance();

        $db->delete('product_specs', 'product_id = :id', ['id' => $productId]);
        $db->delete('product_spec_groups', 'product_id = :id', ['id' => $productId]);

        $groupOrder = 0;

        foreach ($groups as $group) {
            if (!is_array($group)) {
                continue;
            }

            $rows = is_array($group['rows'] ?? null) ? $group['rows'] : [];

            // Skip a group whose rows are all blank.
            $hasContent = false;
            foreach ($rows as $row) {
                foreach ($this->locales() as $locale) {
                    if (trim((string) ($row[$locale]['label'] ?? '')) !== ''
                        || trim((string) ($row[$locale]['value'] ?? '')) !== '') {
                        $hasContent = true;
                        break 2;
                    }
                }
            }

            if (!$hasContent) {
                continue;
            }

            $groupId = $db->insert('product_spec_groups', [
                'product_id' => $productId,
                'sort_order' => $groupOrder++,
            ]);

            foreach ($this->locales() as $locale) {
                $title = trim((string) ($group['title'][$locale] ?? ''));

                if ($title !== '') {
                    $db->insert('product_spec_group_translations', [
                        'group_id' => $groupId,
                        'lang'     => $locale,
                        'title'    => mb_substr($title, 0, 190),
                    ]);
                }
            }

            $rowOrder = 0;

            foreach ($rows as $row) {
                $blank = true;
                foreach ($this->locales() as $locale) {
                    if (trim((string) ($row[$locale]['label'] ?? '')) !== ''
                        || trim((string) ($row[$locale]['value'] ?? '')) !== '') {
                        $blank = false;
                        break;
                    }
                }

                if ($blank) {
                    continue;
                }

                $specId = $db->insert('product_specs', [
                    'product_id' => $productId,
                    'group_id'   => $groupId,
                    'sort_order' => $rowOrder++,
                ]);

                foreach ($this->locales() as $locale) {
                    $label = trim((string) ($row[$locale]['label'] ?? ''));
                    $value = trim((string) ($row[$locale]['value'] ?? ''));

                    if ($label === '' && $value === '') {
                        continue;
                    }

                    $db->insert('product_spec_translations', [
                        'spec_id' => $specId,
                        'lang'    => $locale,
                        'label'   => mb_substr($label, 0, 190),
                        'value'   => mb_substr($value, 0, 500),
                    ]);
                }
            }
        }
    }

    /** Features arrive as features[<i>][<lang>]. */
    private function syncFeatures(int $productId, Request $request): void
    {
        $features = $request->raw('features', []);

        if (!is_array($features)) {
            return;
        }

        $db = Database::instance();
        $db->delete('product_features', 'product_id = :id', ['id' => $productId]);

        $order = 0;

        foreach ($features as $feature) {
            if (!is_array($feature)) {
                continue;
            }

            $blank = true;
            foreach ($this->locales() as $locale) {
                if (trim((string) ($feature[$locale] ?? '')) !== '') {
                    $blank = false;
                    break;
                }
            }

            if ($blank) {
                continue;
            }

            $featureId = $db->insert('product_features', [
                'product_id' => $productId,
                'sort_order' => $order++,
            ]);

            foreach ($this->locales() as $locale) {
                $text = trim((string) ($feature[$locale] ?? ''));

                if ($text === '') {
                    continue;
                }

                $db->insert('product_feature_translations', [
                    'feature_id' => $featureId,
                    'lang'       => $locale,
                    'text'       => mb_substr($text, 0, 500),
                ]);
            }
        }
    }
}
