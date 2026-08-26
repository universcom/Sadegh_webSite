<?php
/** Site footer: brand blurb, quick links, categories, contact details. */

use App\Core\Url;
use App\Models\Category;
use App\Models\Setting;

$categories = Category::published();
$phones     = Setting::phones();
$emails     = Setting::emails();
$socials    = Setting::socialLinks();

$socialIcons = [
    'instagram' => '<rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1.2" fill="currentColor" stroke="none"/>',
    'linkedin'  => '<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/>',
    'telegram'  => '<path d="M22 3 2 10.5l6 2.2L20 6l-9 8.4.4 6L14 17l5 3z"/>',
    'whatsapp'  => '<path d="M21 11.5a8.5 8.5 0 0 1-12.6 7.4L3 21l2.2-5.2A8.5 8.5 0 1 1 21 11.5z"/>',
    'youtube'   => '<rect x="2" y="5" width="20" height="14" rx="4"/><polygon points="10.5 9.5 15.5 12 10.5 14.5" fill="currentColor" stroke="none"/>',
    'x'         => '<path d="M4 4l16 16M20 4L4 20"/>',
    'aparat'    => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3.2"/>',
];
?>
<footer class="site-footer">
    <?php if (!empty($footerSkyline)): ?>
        <img class="site-footer__skyline" src="<?= e($footerSkyline) ?>" alt="" aria-hidden="true" loading="lazy">
    <?php endif; ?>

    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <img class="footer-brand__mark" src="<?= e(asset('img/logo-mark.png')) ?>"
                     alt="" width="46" height="46" aria-hidden="true" loading="lazy">
                <div class="footer-brand__name"><?= e(Setting::get('site_name', 'Rahyaft Sanat')) ?></div>
                <?php if (Setting::get('footer_about') !== ''): ?>
                    <p><?= e(Setting::get('footer_about')) ?></p>
                <?php endif; ?>

                <?php if ($socials !== []): ?>
                    <div class="social-row">
                        <?php foreach ($socials as $network => $href): ?>
                            <a class="social-link" href="<?= e($href) ?>" target="_blank" rel="noopener noreferrer"
                               aria-label="<?= e(ucfirst($network)) ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <?= $socialIcons[$network] ?? '<circle cx="12" cy="12" r="9"/>' ?>
                                </svg>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="footer-col">
                <h2 class="footer-col__title"><?= _e('footer.quick_links') ?></h2>
                <ul>
                    <li><a href="<?= e(Url::home()) ?>"><?= _e('nav.home') ?></a></li>
                    <li><a href="<?= e(Url::products()) ?>"><?= _e('nav.products') ?></a></li>
                    <li><a href="<?= e(Url::research()) ?>"><?= _e('nav.research') ?></a></li>
                    <li><a href="<?= e(Url::about()) ?>"><?= _e('nav.about') ?></a></li>
                    <li><a href="<?= e(Url::contact()) ?>"><?= _e('nav.contact') ?></a></li>
                </ul>
            </div>

            <?php if ($categories !== []): ?>
                <div class="footer-col">
                    <h2 class="footer-col__title"><?= _e('footer.products') ?></h2>
                    <ul>
                        <?php foreach ($categories as $category): ?>
                            <li>
                                <a href="<?= e(Url::category((string) $category['slug'])) ?>">
                                    <?= e($category['name']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="footer-col">
                <h2 class="footer-col__title"><?= _e('footer.contact') ?></h2>
                <ul class="footer-contact">
                    <?php if (Setting::get('address') !== ''): ?>
                        <li>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M21 10c0 7-9 12-9 12s-9-5-9-12a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                            </svg>
                            <span><?= e(Setting::get('address')) ?></span>
                        </li>
                    <?php endif; ?>

                    <?php foreach ($phones as $phone): ?>
                        <li>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.2a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/>
                            </svg>
                            <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $phone)) ?>">
                                <span class="ltr-num"><?= e(num($phone)) ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>

                    <?php foreach ($emails as $email): ?>
                        <li>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 6 10-6"/>
                            </svg>
                            <a href="mailto:<?= e($email) ?>"><span class="ltr-num"><?= e($email) ?></span></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p><?= e(__('footer.rights', ['company' => Setting::get('site_name', 'Rahyaft Sanat')])) ?>
               <span class="ltr-num">© <?= e(num(date('Y'))) ?></span></p>
            <p class="footer-bottom__made">
                <img src="<?= e(asset('img/seal-uni-isfahan.png')) ?>" alt="" width="22" height="22"
                     aria-hidden="true" loading="lazy" style="opacity:.7">
                <?= _e('footer.made_in') ?>
            </p>
        </div>
    </div>
</footer>
