<?php
declare(strict_types=1);

namespace App\Controllers\Site;

use App\Controllers\Controller;
use App\Core\Request;
use App\Core\View;
use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use App\Models\ResearchProject;
use App\Models\Setting;

final class HomeController extends Controller
{
    public function index(Request $request): never
    {
        $page = Page::findBySlug('home') ?? ['sections' => []];

        $data = [
            'page'       => $page,
            'categories' => Category::published(),
            'featured'   => Product::featured(6),
            'research'   => ResearchProject::published(3),
            'canonical'  => $this->canonical($request->path()),
        ];

        $meta = $this->meta($page, 'title', 'subtitle');

        // The home page is the site root: prefer the site-level SEO defaults.
        $data['metaTitle'] = Setting::get('seo_title', '') !== ''
            ? Setting::get('seo_title')
            : Setting::get('site_name', 'Rahyaft Sanat');
        $data['metaDescription'] = $meta['metaDescription'];

        $this->view('site.home', $data);
    }
}
