<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Lang;
use App\Core\Request;
use App\Core\Uploader;
use App\Core\Validator;
use App\Models\Category;
use App\Models\Media;
use App\Models\Model;

final class CategoryController extends AdminController
{
    private const TRANSLATED_FIELDS = ['name', 'description', 'seo_title', 'seo_description'];

    public function index(Request $request): never
    {
        $this->view('admin.categories.index', [
            'pageTitle'  => 'Categories',
            'activeNav'  => 'categories',
            'categories' => Category::allForAdmin(),
        ]);
    }

    public function create(Request $request): never
    {
        $this->view('admin.categories.form', [
            'pageTitle' => 'New category',
            'activeNav' => 'categories',
            'category'  => null,
            'errors'    => [],
        ]);
    }

    public function store(Request $request): never
    {
        $this->requireCsrf($request, $this->adminUrl('categories/create'));

        $translations = $this->translationInput($request, self::TRANSLATED_FIELDS);
        $errors       = $this->validate($request, $translations);

        if ($errors !== []) {
            $this->view('admin.categories.form', [
                'pageTitle' => 'New category',
                'activeNav' => 'categories',
                'category'  => null,
                'errors'    => $errors,
                'old'       => $request->all(),
            ], 422);
        }

        $slug     = $this->resolveSlug($request, $translations, null);
        $imageId  = $this->uploadImage($request);

        $id = Category::create([
            'slug'       => $slug,
            'image_id'   => $imageId,
            'sort_order' => $request->integer('sort_order', 0),
            'is_active'  => $request->boolean('is_active') ? 1 : 0,
        ], $translations);

        $this->back($this->adminUrl('categories/' . $id), 'success', 'Category created.');
    }

    public function edit(Request $request, array $params): never
    {
        $id       = (int) $params['id'];
        $category = Category::find($id);

        if ($category === null) {
            $this->back($this->adminUrl('categories'), 'error', 'That category no longer exists.');
        }

        $category['translations'] = Category::translations($id);
        $category['image']        = $category['image_id'] === null ? null : Media::find((int) $category['image_id']);

        $this->view('admin.categories.form', [
            'pageTitle' => 'Edit category',
            'activeNav' => 'categories',
            'category'  => $category,
            'errors'    => [],
        ]);
    }

    public function update(Request $request, array $params): never
    {
        $id  = (int) $params['id'];
        $url = $this->adminUrl('categories/' . $id);

        $this->requireCsrf($request, $url);

        $existing = Category::find($id);
        if ($existing === null) {
            $this->back($this->adminUrl('categories'), 'error', 'That category no longer exists.');
        }

        $translations = $this->translationInput($request, self::TRANSLATED_FIELDS);
        $errors       = $this->validate($request, $translations, $id);

        if ($errors !== []) {
            $existing['translations'] = Category::translations($id);
            $existing['image']        = $existing['image_id'] === null ? null : Media::find((int) $existing['image_id']);

            $this->view('admin.categories.form', [
                'pageTitle' => 'Edit category',
                'activeNav' => 'categories',
                'category'  => $existing,
                'errors'    => $errors,
                'old'       => $request->all(),
            ], 422);
        }

        $attributes = [
            'slug'       => $this->resolveSlug($request, $translations, $id),
            'sort_order' => $request->integer('sort_order', 0),
            'is_active'  => $request->boolean('is_active') ? 1 : 0,
        ];

        $imageId = $this->uploadImage($request);
        if ($imageId !== null) {
            $attributes['image_id'] = $imageId;
        }

        Category::update($id, $attributes, $translations);

        $this->back($url, 'success', 'Category saved.');
    }

    public function destroy(Request $request, array $params): never
    {
        $this->requireCsrf($request, $this->adminUrl('categories'));
        $this->requireAdmin($this->adminUrl('categories'));

        // Products in the category survive; the foreign key clears their link.
        Category::delete((int) $params['id']);

        $this->back($this->adminUrl('categories'), 'success', 'Category deleted. Its products were kept and are now uncategorised.');
    }

    // --- Internals ----------------------------------------------------------

    private function validate(Request $request, array $translations, ?int $ignoreId = null): array
    {
        $errors  = [];
        $default = Lang::default();

        if (trim($translations[$default]['name'] ?? '') === '') {
            $errors['name'] = sprintf('The category name is required in %s.', strtoupper($default));
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
            $source = $translations['en']['name'] ?? '';
            if (trim($source) === '') {
                $source = $translations[Lang::default()]['name'] ?? 'category';
            }
            $slug = slugify($source, 'category');
        }

        return Model::uniqueSlug('categories', $slug, $ignoreId);
    }

    private function uploadImage(Request $request): ?int
    {
        $file = $request->file('image');

        if (!Uploader::wasUploaded($file)) {
            return null;
        }

        try {
            return Media::storeImage($file);
        } catch (\Throwable $e) {
            \App\Core\Session::flash('error', 'Image not saved: ' . $e->getMessage());

            return null;
        }
    }
}
