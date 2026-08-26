<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Uploader;
use App\Models\Media;

final class MediaController extends AdminController
{
    public function index(Request $request): never
    {
        $kind   = (string) ($request->query('kind') ?? '');
        $search = (string) ($request->query('q') ?? '');

        $this->view('admin.media.index', [
            'pageTitle' => 'Media library',
            'activeNav' => 'media',
            'result'    => Media::listing($kind, max(1, $request->integer('page', 1)), 24, $search),
            'kind'      => $kind,
            'search'    => $search,
        ]);
    }

    public function upload(Request $request): never
    {
        $url = $this->adminUrl('media');
        $this->requireCsrf($request, $url);

        $file = $request->file('file');

        if (!Uploader::wasUploaded($file)) {
            $this->back($url, 'error', 'Please choose a file to upload.');
        }

        $extension = strtolower((string) pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        $isImage   = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);

        try {
            if ($isImage) {
                Media::storeImage($file, [
                    'fa' => (string) $request->input('alt_fa', '') ?: null,
                    'en' => (string) $request->input('alt_en', '') ?: null,
                    'ar' => (string) $request->input('alt_ar', '') ?: null,
                ]);
            } else {
                Media::storeDocument($file);
            }
        } catch (\Throwable $e) {
            $this->back($url, 'error', $e->getMessage());
        }

        $this->back($url, 'success', 'File uploaded to the media library.');
    }

    public function updateAlt(Request $request, array $params): never
    {
        $url = $this->adminUrl('media');
        $this->requireCsrf($request, $url);

        Media::updateAlt((int) $params['id'], [
            'fa' => (string) $request->input('alt_fa', '') ?: null,
            'en' => (string) $request->input('alt_en', '') ?: null,
            'ar' => (string) $request->input('alt_ar', '') ?: null,
        ]);

        $this->back($url, 'success', 'Alt text updated.');
    }

    public function destroy(Request $request, array $params): never
    {
        $url = $this->adminUrl('media');
        $this->requireCsrf($request, $url);
        $this->requireAdmin($url);

        $id    = (int) $params['id'];
        $usage = Media::usageCount($id);

        // Refuse to break a page that still points at this file.
        if ($usage > 0) {
            $this->back($url, 'error', sprintf(
                'That file is still used in %d place%s. Remove those references first.',
                $usage,
                $usage === 1 ? '' : 's'
            ));
        }

        Media::delete($id);

        $this->back($url, 'success', 'File deleted.');
    }
}
