<?php
declare(strict_types=1);

/**
 * Route table.
 *
 * Public routes are language-prefixed: /{lang}/products/{slug}. The prefix is
 * validated by the {lang} pattern so an unknown prefix falls through to the
 * 404 handler rather than being treated as a page slug.
 *
 * Admin routes are deliberately unprefixed — the panel is a single-operator
 * surface and its language follows the operator's last choice.
 */

use App\Controllers\Admin;
use App\Controllers\Site;
use App\Core\Auth;
use App\Core\Lang;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\Url;

return static function (Router $router): void {
    $lang = implode('|', Lang::enabled());

    // --- Machine-readable endpoints ----------------------------------------
    $router->get('/sitemap.xml', [Site\SitemapController::class, 'sitemap']);
    $router->get('/robots.txt',  [Site\SitemapController::class, 'robots']);

    // --- Public site --------------------------------------------------------
    $router->get('/{lang:' . $lang . '}',          [Site\HomeController::class, 'index']);
    $router->get('/{lang:' . $lang . '}/',         [Site\HomeController::class, 'index']);

    $router->get('/{lang:' . $lang . '}/products', [Site\ProductController::class, 'index']);
    $router->get('/{lang:' . $lang . '}/products/category/{category}', [Site\ProductController::class, 'index']);
    $router->get('/{lang:' . $lang . '}/products/{slug}', [Site\ProductController::class, 'show']);

    $router->get('/{lang:' . $lang . '}/research', [Site\ResearchController::class, 'index']);
    $router->get('/{lang:' . $lang . '}/research/{slug}', [Site\ResearchController::class, 'show']);

    $router->get('/{lang:' . $lang . '}/about',    [Site\PageController::class, 'about']);

    $router->get('/{lang:' . $lang . '}/contact',  [Site\ContactController::class, 'show']);
    $router->post('/{lang:' . $lang . '}/contact', [Site\ContactController::class, 'submit']);

    // --- Administration -----------------------------------------------------
    // Every admin route except the login screen passes through this guard.
    $guard = static function (Request $request): void {
        if (!Auth::check($request)) {
            \App\Core\Session::put('_intended', $request->path());
            Response::redirect(Url::admin('login'));
        }
    };

    $router->get('/admin/login',  [Admin\AuthController::class, 'showLogin']);
    $router->post('/admin/login', [Admin\AuthController::class, 'login']);
    $router->post('/admin/logout', [Admin\AuthController::class, 'logout']);

    $router->get('/admin',           [Admin\DashboardController::class, 'index'], [$guard]);
    $router->get('/admin/dashboard', [Admin\DashboardController::class, 'index'], [$guard]);

    // Products
    $router->get('/admin/products',              [Admin\ProductController::class, 'index'],  [$guard]);
    $router->get('/admin/products/create',       [Admin\ProductController::class, 'create'], [$guard]);
    $router->post('/admin/products/create',      [Admin\ProductController::class, 'store'],  [$guard]);
    $router->get('/admin/products/{id:\d+}',     [Admin\ProductController::class, 'edit'],   [$guard]);
    $router->post('/admin/products/{id:\d+}',    [Admin\ProductController::class, 'update'], [$guard]);
    $router->post('/admin/products/{id:\d+}/delete', [Admin\ProductController::class, 'destroy'], [$guard]);
    $router->post('/admin/products/{id:\d+}/status', [Admin\ProductController::class, 'status'],  [$guard]);
    $router->post('/admin/products/{id:\d+}/images',  [Admin\ProductController::class, 'uploadImage'], [$guard]);
    $router->post('/admin/products/{id:\d+}/images/{imageId:\d+}/delete', [Admin\ProductController::class, 'deleteImage'], [$guard]);
    $router->post('/admin/products/{id:\d+}/downloads', [Admin\ProductController::class, 'uploadDownload'], [$guard]);
    $router->post('/admin/products/{id:\d+}/downloads/{downloadId:\d+}/delete', [Admin\ProductController::class, 'deleteDownload'], [$guard]);

    // Categories
    $router->get('/admin/categories',              [Admin\CategoryController::class, 'index'],  [$guard]);
    $router->get('/admin/categories/create',       [Admin\CategoryController::class, 'create'], [$guard]);
    $router->post('/admin/categories/create',      [Admin\CategoryController::class, 'store'],  [$guard]);
    $router->get('/admin/categories/{id:\d+}',     [Admin\CategoryController::class, 'edit'],   [$guard]);
    $router->post('/admin/categories/{id:\d+}',    [Admin\CategoryController::class, 'update'], [$guard]);
    $router->post('/admin/categories/{id:\d+}/delete', [Admin\CategoryController::class, 'destroy'], [$guard]);

    // Research projects
    $router->get('/admin/research',              [Admin\ResearchController::class, 'index'],  [$guard]);
    $router->get('/admin/research/create',       [Admin\ResearchController::class, 'create'], [$guard]);
    $router->post('/admin/research/create',      [Admin\ResearchController::class, 'store'],  [$guard]);
    $router->get('/admin/research/{id:\d+}',     [Admin\ResearchController::class, 'edit'],   [$guard]);
    $router->post('/admin/research/{id:\d+}',    [Admin\ResearchController::class, 'update'], [$guard]);
    $router->post('/admin/research/{id:\d+}/delete', [Admin\ResearchController::class, 'destroy'], [$guard]);
    $router->post('/admin/research/{id:\d+}/images', [Admin\ResearchController::class, 'uploadImage'], [$guard]);
    $router->post('/admin/research/{id:\d+}/images/{imageId:\d+}/delete', [Admin\ResearchController::class, 'deleteImage'], [$guard]);

    // Pages
    $router->get('/admin/pages',                     [Admin\PageController::class, 'index'], [$guard]);
    $router->get('/admin/pages/{id:\d+}',            [Admin\PageController::class, 'edit'],  [$guard]);
    $router->post('/admin/pages/{id:\d+}',           [Admin\PageController::class, 'update'], [$guard]);
    $router->post('/admin/pages/{id:\d+}/sections',  [Admin\PageController::class, 'saveSection'], [$guard]);
    $router->post('/admin/pages/{id:\d+}/sections/{sectionId:\d+}/delete', [Admin\PageController::class, 'deleteSection'], [$guard]);

    // Messages
    $router->get('/admin/messages',                [Admin\MessageController::class, 'index'],  [$guard]);
    $router->get('/admin/messages/export',         [Admin\MessageController::class, 'export'], [$guard]);
    $router->get('/admin/messages/{id:\d+}',       [Admin\MessageController::class, 'show'],   [$guard]);
    $router->post('/admin/messages/{id:\d+}/status', [Admin\MessageController::class, 'status'], [$guard]);
    $router->post('/admin/messages/{id:\d+}/delete', [Admin\MessageController::class, 'destroy'], [$guard]);

    // Media
    $router->get('/admin/media',                 [Admin\MediaController::class, 'index'],  [$guard]);
    $router->post('/admin/media/upload',         [Admin\MediaController::class, 'upload'], [$guard]);
    $router->post('/admin/media/{id:\d+}/alt',   [Admin\MediaController::class, 'updateAlt'], [$guard]);
    $router->post('/admin/media/{id:\d+}/delete', [Admin\MediaController::class, 'destroy'], [$guard]);

    // Settings & users
    $router->get('/admin/settings',       [Admin\SettingController::class, 'index'],  [$guard]);
    $router->post('/admin/settings',      [Admin\SettingController::class, 'update'], [$guard]);
    $router->get('/admin/users',          [Admin\UserController::class, 'index'],   [$guard]);
    $router->post('/admin/users',         [Admin\UserController::class, 'store'],   [$guard]);
    $router->post('/admin/users/{id:\d+}', [Admin\UserController::class, 'update'], [$guard]);
    $router->post('/admin/users/{id:\d+}/delete', [Admin\UserController::class, 'destroy'], [$guard]);
    $router->get('/admin/profile',        [Admin\UserController::class, 'profile'], [$guard]);
    $router->post('/admin/profile',       [Admin\UserController::class, 'updateProfile'], [$guard]);

    // --- Fallback -----------------------------------------------------------
    $router->fallback(static function (Request $request): void {
        (new Site\NotFoundController())->show($request);
    });
};
