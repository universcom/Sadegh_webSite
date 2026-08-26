<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Request;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Media;
use App\Models\Page;
use App\Models\Product;
use App\Models\ResearchProject;

final class DashboardController extends AdminController
{
    public function index(Request $request): never
    {
        $db = Database::instance();

        $this->view('admin.dashboard', [
            'pageTitle' => 'Dashboard',
            'activeNav' => 'dashboard',
            'stats' => [
                'products'   => Product::count('published'),
                'drafts'     => Product::count('draft'),
                'categories' => Category::count(),
                'research'   => ResearchProject::count(),
                'media'      => Media::count(),
                'messages'   => ContactMessage::total(),
                'unread'     => ContactMessage::unreadCount(),
            ],
            'recentMessages' => ContactMessage::recent(6),
            'recentPages'    => Page::recentlyUpdated(4),
            'recentProducts' => $db->all(
                'SELECT p.id, p.slug, p.updated_at, p.status, t.name
                 FROM products p
                 LEFT JOIN product_translations t ON t.product_id = p.id AND t.lang = :lang
                 ORDER BY p.updated_at DESC LIMIT 5',
                ['lang' => \App\Core\Lang::default()]
            ),
            // Imported entries an operator was asked to verify.
            'needsReview' => $db->all(
                'SELECT p.id, p.slug, t.name
                 FROM products p
                 LEFT JOIN product_translations t ON t.product_id = p.id AND t.lang = :lang
                 WHERE p.needs_review = 1',
                ['lang' => \App\Core\Lang::default()]
            ),
        ]);
    }
}
