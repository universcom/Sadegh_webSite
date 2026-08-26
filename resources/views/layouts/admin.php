<?php
/**
 * Administration layout: sidebar, top bar, flash messages.
 *
 * @var string $content
 * @var array  $admin
 * @var int    $unreadCount
 * @var string $activeNav
 * @var string $pageTitle
 */

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Url;
use App\Core\View;
use App\Models\Setting;

$icons = [
    'dashboard' => '<rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>',
    'products'  => '<path d="M12 2 3 7v10l9 5 9-5V7z"/><path d="m3 7 9 5 9-5"/><path d="M12 22V12"/>',
    'categories'=> '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
    'research'  => '<path d="M9 2v6L4.5 17A2 2 0 0 0 6.3 20h11.4a2 2 0 0 0 1.8-3L15 8V2"/><path d="M8 2h8"/><path d="M7.5 14h9"/>',
    'pages'     => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h8"/><path d="M8 17h5"/>',
    'messages'  => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 6 10-6"/>',
    'media'     => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.8"/><path d="m21 15-5-5L5 21"/>',
    'settings'  => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.9.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1A1.7 1.7 0 0 0 4.6 8.6a1.7 1.7 0 0 0-.3-1.9l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.9.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.9-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.9V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>',
    'users'     => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9"/><path d="M16 3.1a4 4 0 0 1 0 7.8"/>',
];

$nav = [
    ['key' => 'dashboard',  'label' => 'Dashboard',   'url' => Url::admin(),            'icon' => 'dashboard'],
    ['key' => 'products',   'label' => 'Products',    'url' => Url::admin('products'),  'icon' => 'products'],
    ['key' => 'categories', 'label' => 'Categories',  'url' => Url::admin('categories'),'icon' => 'categories'],
    ['key' => 'research',   'label' => 'R&D projects','url' => Url::admin('research'),  'icon' => 'research'],
    ['key' => 'pages',      'label' => 'Pages',       'url' => Url::admin('pages'),     'icon' => 'pages'],
    ['key' => 'messages',   'label' => 'Messages',    'url' => Url::admin('messages'),  'icon' => 'messages', 'badge' => $unreadCount],
    ['key' => 'media',      'label' => 'Media',       'url' => Url::admin('media'),     'icon' => 'media'],
];

$navSettings = [
    ['key' => 'settings', 'label' => 'Site settings', 'url' => Url::admin('settings'), 'icon' => 'settings'],
];

if (Auth::isOwner()) {
    $navSettings[] = ['key' => 'users', 'label' => 'Administrators', 'url' => Url::admin('users'), 'icon' => 'users'];
}

$initials = mb_strtoupper(mb_substr((string) ($admin['name'] ?? '?'), 0, 1));

$renderNav = static function (array $items) use ($activeNav, $icons): void {
    foreach ($items as $item) {
        $isActive = $activeNav === $item['key'];
        printf(
            '<a class="nav-item%s" href="%s"%s>'
            . '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">%s</svg>'
            . '<span>%s</span>%s</a>',
            $isActive ? ' is-active' : '',
            e($item['url']),
            $isActive ? ' aria-current="page"' : '',
            $icons[$item['icon']] ?? '',
            e($item['label']),
            !empty($item['badge'])
                ? '<span class="nav-item__badge">' . e((string) $item['badge']) . '</span>'
                : ''
        );
    }
};
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($pageTitle !== '' ? $pageTitle . ' · Admin' : 'Admin') ?> · <?= e(Setting::get('site_name', 'Rahyaft Sanat')) ?></title>
    <link rel="icon" href="<?= e(asset('img/favicon-32.png')) ?>" sizes="32x32">
    <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body>
<div class="shell">
    <aside class="sidebar">
        <a class="sidebar__brand" href="<?= e(Url::admin()) ?>">
            <img src="<?= e(asset('img/logo-mark.png')) ?>" alt="" width="36" height="36">
            <span>
                <strong><?= e(Setting::get('site_name', 'Rahyaft Sanat')) ?></strong>
                <span>Administration</span>
            </span>
        </a>

        <nav aria-label="Admin">
            <p class="sidebar__label">Content</p>
            <?php $renderNav($nav); ?>
            <p class="sidebar__label">Configuration</p>
            <?php $renderNav($navSettings); ?>
        </nav>

        <div class="sidebar__foot">
            <a href="<?= e(Url::home()) ?>" target="_blank" rel="noopener">View website ↗</a>
            <a href="<?= e(Url::admin('profile')) ?>">My profile</a>
        </div>
    </aside>

    <div class="scrim"></div>

    <div class="main">
        <header class="topbar">
            <button type="button" class="menu-toggle" data-menu-toggle aria-expanded="false" aria-label="Toggle navigation">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" width="20" height="20" aria-hidden="true">
                    <path d="M3 6h18M3 12h18M3 18h18"/>
                </svg>
            </button>

            <h1><?= e($pageTitle !== '' ? $pageTitle : 'Dashboard') ?></h1>

            <div class="topbar__user">
                <span class="avatar" aria-hidden="true"><?= e($initials) ?></span>
                <span class="nowrap"><?= e($admin['name'] ?? '') ?></span>
            </div>

            <form method="post" action="<?= e(Url::admin('logout')) ?>" class="inline-form">
                <?= Csrf::field() ?>
                <button class="btn ghost sm" type="submit">Sign out</button>
            </form>
        </header>

        <main class="content">
            <?= View::partial('admin.partials.flash') ?>
            <?= $content ?>
        </main>
    </div>
</div>

<script src="<?= e(asset('js/admin.js')) ?>" defer></script>
</body>
</html>
