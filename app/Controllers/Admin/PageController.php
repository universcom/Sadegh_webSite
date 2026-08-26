<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Core\Uploader;
use App\Models\Media;
use App\Models\Page;

final class PageController extends AdminController
{
    private const PAGE_FIELDS    = ['title', 'subtitle', 'body', 'seo_title', 'seo_description'];
    private const SECTION_FIELDS = ['heading', 'subheading', 'body', 'cta_label', 'cta_url'];

    public function index(Request $request): never
    {
        $this->view('admin.pages.index', [
            'pageTitle' => 'Pages',
            'activeNav' => 'pages',
            'pages'     => Page::allForAdmin(),
        ]);
    }

    public function edit(Request $request, array $params): never
    {
        $page = Page::findForAdmin((int) $params['id']);

        if ($page === null) {
            $this->back($this->adminUrl('pages'), 'error', 'That page no longer exists.');
        }

        $this->view('admin.pages.form', [
            'pageTitle' => 'Edit page',
            'activeNav' => 'pages',
            'page'      => $page,
        ]);
    }

    public function update(Request $request, array $params): never
    {
        $id  = (int) $params['id'];
        $url = $this->adminUrl('pages/' . $id);

        $this->requireCsrf($request, $url);

        if (Page::findForAdmin($id) === null) {
            $this->back($this->adminUrl('pages'), 'error', 'That page no longer exists.');
        }

        Page::updateTranslations($id, $this->translationInput($request, self::PAGE_FIELDS));

        $this->back($url, 'success', 'Page saved.');
    }

    public function saveSection(Request $request, array $params): never
    {
        $pageId = (int) $params['id'];
        $url    = $this->adminUrl('pages/' . $pageId);

        $this->requireCsrf($request, $url);

        $type = (string) $request->input('type', '');

        if (!in_array($type, Page::SECTION_TYPES, true)) {
            $this->back($url, 'error', 'That section type is not recognised.');
        }

        $sectionId = $request->integer('section_id', 0);

        $attributes = [
            'type'       => $type,
            'sort_order' => $request->integer('sort_order', 0),
            'is_active'  => $request->boolean('is_active') ? 1 : 0,
        ];

        // A new image replaces the existing one; leaving the field empty keeps it.
        $file = $request->file('media');
        if (Uploader::wasUploaded($file)) {
            try {
                $attributes['media_id'] = Media::storeImage($file);
            } catch (\Throwable $e) {
                Session::flash('error', 'Section image not saved: ' . $e->getMessage());
            }
        } elseif ($request->boolean('remove_media')) {
            $attributes['media_id'] = null;
        }

        Page::saveSection(
            $pageId,
            $sectionId > 0 ? $sectionId : null,
            $attributes,
            $this->translationInput($request, self::SECTION_FIELDS)
        );

        $this->back($url, 'success', 'Section saved.');
    }

    public function deleteSection(Request $request, array $params): never
    {
        $pageId = (int) $params['id'];
        $url    = $this->adminUrl('pages/' . $pageId);

        $this->requireCsrf($request, $url);

        Page::deleteSection($pageId, (int) $params['sectionId']);

        $this->back($url, 'success', 'Section deleted.');
    }
}
