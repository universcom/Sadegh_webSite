<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Lang;
use App\Core\Request;
use App\Core\Session;
use App\Core\Uploader;
use App\Core\Validator;
use App\Models\Media;
use App\Models\Model;
use App\Models\ResearchProject;

final class ResearchController extends AdminController
{
    private const TRANSLATED_FIELDS = ['title', 'summary', 'body', 'seo_title', 'seo_description'];

    public function index(Request $request): never
    {
        $this->view('admin.research.index', [
            'pageTitle' => 'R&D projects',
            'activeNav' => 'research',
            'projects'  => ResearchProject::allForAdmin(),
        ]);
    }

    public function create(Request $request): never
    {
        $this->view('admin.research.form', [
            'pageTitle' => 'New R&D project',
            'activeNav' => 'research',
            'project'   => null,
            'errors'    => [],
        ]);
    }

    public function store(Request $request): never
    {
        $this->requireCsrf($request, $this->adminUrl('research/create'));

        $translations = $this->translationInput($request, self::TRANSLATED_FIELDS);
        $errors       = $this->validate($request, $translations);

        if ($errors !== []) {
            $this->view('admin.research.form', [
                'pageTitle' => 'New R&D project',
                'activeNav' => 'research',
                'project'   => null,
                'errors'    => $errors,
                'old'       => $request->all(),
            ], 422);
        }

        $id = ResearchProject::create([
            'slug'           => $this->resolveSlug($request, $translations, null),
            'cover_image_id' => $this->uploadCover($request),
            'sort_order'     => $request->integer('sort_order', 0),
            'status'         => $request->input('status') === 'draft' ? 'draft' : 'published',
        ], $translations);

        $this->back($this->adminUrl('research/' . $id), 'success', 'Project created. You can now add gallery images.');
    }

    public function edit(Request $request, array $params): never
    {
        $project = ResearchProject::findForAdmin((int) $params['id']);

        if ($project === null) {
            $this->back($this->adminUrl('research'), 'error', 'That project no longer exists.');
        }

        $this->view('admin.research.form', [
            'pageTitle' => 'Edit R&D project',
            'activeNav' => 'research',
            'project'   => $project,
            'errors'    => [],
        ]);
    }

    public function update(Request $request, array $params): never
    {
        $id  = (int) $params['id'];
        $url = $this->adminUrl('research/' . $id);

        $this->requireCsrf($request, $url);

        $project = ResearchProject::findForAdmin($id);
        if ($project === null) {
            $this->back($this->adminUrl('research'), 'error', 'That project no longer exists.');
        }

        $translations = $this->translationInput($request, self::TRANSLATED_FIELDS);
        $errors       = $this->validate($request, $translations, $id);

        if ($errors !== []) {
            $this->view('admin.research.form', [
                'pageTitle' => 'Edit R&D project',
                'activeNav' => 'research',
                'project'   => $project,
                'errors'    => $errors,
                'old'       => $request->all(),
            ], 422);
        }

        $attributes = [
            'slug'       => $this->resolveSlug($request, $translations, $id),
            'sort_order' => $request->integer('sort_order', 0),
            'status'     => $request->input('status') === 'draft' ? 'draft' : 'published',
        ];

        $cover = $this->uploadCover($request);
        if ($cover !== null) {
            $attributes['cover_image_id'] = $cover;
        }

        ResearchProject::update($id, $attributes, $translations);

        $this->back($url, 'success', 'Project saved.');
    }

    public function destroy(Request $request, array $params): never
    {
        $this->requireCsrf($request, $this->adminUrl('research'));
        $this->requireAdmin($this->adminUrl('research'));

        ResearchProject::delete((int) $params['id']);

        $this->back($this->adminUrl('research'), 'success', 'Project deleted.');
    }

    public function uploadImage(Request $request, array $params): never
    {
        $id  = (int) $params['id'];
        $url = $this->adminUrl('research/' . $id);

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
            'SELECT COALESCE(MAX(sort_order), -1) + 1 FROM research_project_images WHERE project_id = :id',
            ['id' => $id]
        );

        $db->insert('research_project_images', [
            'project_id' => $id,
            'media_id'   => $mediaId,
            'sort_order' => $next,
        ]);

        if ($db->value('SELECT cover_image_id FROM research_projects WHERE id = :id', ['id' => $id]) === null) {
            $db->update('research_projects', ['cover_image_id' => $mediaId], 'id = :id', ['id' => $id]);
        }

        $this->back($url, 'success', 'Image added to the project gallery.');
    }

    public function deleteImage(Request $request, array $params): never
    {
        $id  = (int) $params['id'];
        $url = $this->adminUrl('research/' . $id);

        $this->requireCsrf($request, $url);

        $db      = Database::instance();
        $mediaId = (int) $params['imageId'];

        $db->delete('research_project_images', 'project_id = :p AND media_id = :m', ['p' => $id, 'm' => $mediaId]);

        $cover = $db->value('SELECT cover_image_id FROM research_projects WHERE id = :id', ['id' => $id]);
        if ((int) $cover === $mediaId) {
            $replacement = $db->value(
                'SELECT media_id FROM research_project_images WHERE project_id = :id ORDER BY sort_order ASC LIMIT 1',
                ['id' => $id]
            );
            $db->update('research_projects', ['cover_image_id' => $replacement], 'id = :id', ['id' => $id]);
        }

        $this->back($url, 'success', 'Image removed.');
    }

    // --- Internals ----------------------------------------------------------

    private function validate(Request $request, array $translations, ?int $ignoreId = null): array
    {
        $errors  = [];
        $default = Lang::default();

        if (trim($translations[$default]['title'] ?? '') === '') {
            $errors['title'] = sprintf('The project title is required in %s.', strtoupper($default));
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
            $source = $translations['en']['title'] ?? '';
            if (trim($source) === '') {
                $source = $translations[Lang::default()]['title'] ?? 'project';
            }
            $slug = slugify($source, 'project');
        }

        return Model::uniqueSlug('research_projects', $slug, $ignoreId);
    }

    private function uploadCover(Request $request): ?int
    {
        $file = $request->file('cover');

        if (!Uploader::wasUploaded($file)) {
            return null;
        }

        try {
            return Media::storeImage($file);
        } catch (\Throwable $e) {
            Session::flash('error', 'Cover image not saved: ' . $e->getMessage());

            return null;
        }
    }
}
