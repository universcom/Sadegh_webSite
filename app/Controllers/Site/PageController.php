<?php
declare(strict_types=1);

namespace App\Controllers\Site;

use App\Controllers\Controller;
use App\Core\Lang;
use App\Core\Request;
use App\Core\Url;
use App\Core\View;
use App\Models\Page;

final class PageController extends Controller
{
    public function about(Request $request): never
    {
        $page = Page::findBySlug('about');

        if ($page === null) {
            $this->notFound();
        }

        View::start('schema');
        echo '<script type="application/ld+json">' . $this->breadcrumbSchema([
            ['name' => Lang::get('common.home'), 'url' => Url::home()],
            ['name' => Lang::get('nav.about'),   'url' => Url::about()],
        ]) . '</script>';
        View::stop();

        $this->view('site.about', $this->meta($page, 'title', 'subtitle') + [
            'page'      => $page,
            'canonical' => $this->canonical($request->path()),
        ]);
    }
}
