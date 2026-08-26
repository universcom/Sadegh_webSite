<?php
declare(strict_types=1);

namespace App\Controllers\Site;

use App\Controllers\Controller;
use App\Core\Lang;
use App\Core\Request;
use App\Core\Url;
use App\Core\View;
use App\Models\Page;
use App\Models\ResearchProject;

final class ResearchController extends Controller
{
    public function index(Request $request): never
    {
        $page = Page::findBySlug('research');

        $meta = $page !== null
            ? $this->meta($page, 'title', 'subtitle')
            : ['metaTitle' => Lang::get('research.title'), 'metaDescription' => Lang::get('research.lead')];

        View::start('schema');
        echo '<script type="application/ld+json">' . $this->breadcrumbSchema([
            ['name' => Lang::get('common.home'),    'url' => Url::home()],
            ['name' => Lang::get('nav.research'),   'url' => Url::research()],
        ]) . '</script>';
        View::stop();

        $this->view('site.research', $meta + [
            'page'      => $page,
            'projects'  => ResearchProject::published(),
            'canonical' => $this->canonical($request->path()),
        ]);
    }

    public function show(Request $request, array $params): never
    {
        $project = ResearchProject::findBySlug((string) ($params['slug'] ?? ''));

        if ($project === null) {
            $this->notFound();
        }

        $image = (string) ($project['image_path'] ?? '');

        View::start('schema');
        echo '<script type="application/ld+json">' . $this->breadcrumbSchema([
            ['name' => Lang::get('common.home'),  'url' => Url::home()],
            ['name' => Lang::get('nav.research'), 'url' => Url::research()],
            ['name' => (string) $project['title'], 'url' => Url::researchProject((string) $project['slug'])],
        ]) . '</script>';
        View::stop();

        $this->view('site.research-project', $this->meta($project, 'title', 'summary') + [
            'project'   => $project,
            'siblings'  => ResearchProject::siblings((int) $project['id']),
            'canonical' => $this->canonical($request->path()),
            'ogType'    => 'article',
            'ogImage'   => $image !== '' ? Url::upload($image) : null,
        ]);
    }
}
