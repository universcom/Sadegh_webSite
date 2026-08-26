<?php
/** Site header: brand, primary navigation, language switcher. */

use App\Core\Lang;
use App\Core\Url;
use App\Models\Setting;

$path    = $request->path();
$current = static function (string $needle) use ($path): bool {
    $segments = array_values(array_filter(explode('/', $needle), static fn ($s) => $s !== ''));
    $section  = $segments[1] ?? '';

    $active = array_values(array_filter(explode('/', $path), static fn ($s) => $s !== ''));

    // Home is active only on an exact match; other items match their section.
    return $section === ''
        ? count($active) <= 1
        : ($active[1] ?? '') === $section;
};

$links = [
    ['url' => Url::home(),     'label' => __('nav.home')],
    ['url' => Url::products(), 'label' => __('nav.products')],
    ['url' => Url::research(), 'label' => __('nav.research')],
    ['url' => Url::about(),    'label' => __('nav.about')],
    ['url' => Url::contact(),  'label' => __('nav.contact')],
];
?>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="<?= e(Url::home()) ?>">
            <img class="brand__mark" src="<?= e(asset('img/logo-mark.png')) ?>"
                 alt="" width="42" height="42" aria-hidden="true">
            <?php /* The strapline is deliberately not repeated here: it appears in
                     the hero and the footer, and in the header it crowded the
                     navigation in every language. */ ?>
            <span class="brand__text">
                <span class="brand__name"><?= e(Setting::get('site_name', 'Rahyaft Sanat')) ?></span>
            </span>
        </a>

        <nav class="nav" id="primary-nav" aria-label="<?= _e('nav.menu') ?>">
            <ul class="nav__list">
                <?php foreach ($links as $link): ?>
                    <?php $isActive = $current($link['url']); ?>
                    <li>
                        <a class="nav__link<?= $isActive ? ' is-active' : '' ?>"
                           href="<?= e($link['url']) ?>"
                           <?= $isActive ? 'aria-current="page"' : '' ?>><?= e($link['label']) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <div class="header-actions">
            <?php if (count($locales) > 1): ?>
                <div class="lang-switch" data-lang-switch>
                    <button type="button" class="lang-switch__toggle" data-lang-toggle
                            aria-expanded="false" aria-haspopup="true"
                            aria-label="<?= _e('common.language') ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"/><path d="M2 12h20"/>
                            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                        </svg>
                        <span><?= e(Lang::nativeName($locale)) ?></span>
                        <svg class="lang-switch__caret" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
                             width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="lang-switch__menu" role="menu">
                        <?php foreach ($locales as $code): ?>
                            <a class="lang-switch__item" role="menuitem"
                               href="<?= e(Url::switchLocale($code, $request)) ?>"
                               hreflang="<?= e(Lang::htmlLang($code)) ?>"
                               lang="<?= e(Lang::htmlLang($code)) ?>"
                               <?= $code === $locale ? 'aria-current="true"' : '' ?>>
                                <span><?= e(Lang::nativeName($code)) ?></span>
                                <span class="lang-switch__code"><?= e($code) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php /* Distinct from the nav's Contact link, which sits alongside it. */ ?>
            <a class="btn btn--accent btn--sm" href="<?= e(Url::contact()) ?>">
                <?= _e('common.get_quote') ?>
            </a>

            <button type="button" class="nav-toggle" data-nav-toggle
                    aria-expanded="false" aria-controls="primary-nav"
                    aria-label="<?= _e('nav.open_menu') ?>">
                <span class="nav-toggle__bars" aria-hidden="true">
                    <span></span><span></span><span></span>
                </span>
            </button>
        </div>
    </div>
</header>
