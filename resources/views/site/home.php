<?php
/**
 * Home page.
 *
 * Editorial copy comes from page_sections (hero, intro, capabilities, quote,
 * cta) so it is all editable in the admin; the category, product and R&D rails
 * are generated from live data.
 *
 * @var array $page
 * @var array $categories
 * @var array $featured
 * @var array $research
 */

use App\Core\Url;
use App\Core\View;
use App\Models\Page;
use App\Models\Setting;

$sections     = $page['sections'] ?? [];
$hero         = Page::firstOfType($sections, 'hero');
$intro        = Page::firstOfType($sections, 'image_text');
$capabilities = Page::firstOfType($sections, 'features');
$stats        = Page::firstOfType($sections, 'stats');
$quote        = Page::firstOfType($sections, 'quote');
$cta          = Page::firstOfType($sections, 'cta');

$capabilityIcons = [
    '<path d="M12 2 3 7v10l9 5 9-5V7z"/><path d="m3 7 9 5 9-5"/><path d="M12 22V12"/>',
    '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.9.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1A1.7 1.7 0 0 0 4.6 8.6a1.7 1.7 0 0 0-.3-1.9l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.9.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.9-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.9V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>',
    '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.8-3.8a6 6 0 0 1-7.9 7.9l-6.9 6.9a2.1 2.1 0 0 1-3-3l6.9-6.9a6 6 0 0 1 7.9-7.9z"/>',
    '<path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/>',
    '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
    '<path d="m7.5 4.3 9 5.2v10.4l-9-5.2z"/><path d="m16.5 9.5 4-2.3"/><path d="M7.5 4.3 3.5 6.6v10.4l4 2.3"/>',
];
?>

<?php /* ---------------------------------------------------------------- Hero */ ?>
<section class="hero">
    <?php if (!empty($hero['image_path'])): ?>
        <div class="hero__media">
            <?= View::partial('partials.image', [
                'media' => $hero,
                'sizes' => '100vw',
                'eager' => true,
                'fallback' => '',
            ]) ?>
        </div>
    <?php endif; ?>

    <div class="container hero__content">
        <p class="hero__eyebrow"><?= e($hero['subheading'] ?? __('home.hero.eyebrow')) ?></p>
        <h1><?= e($hero['heading'] ?? Setting::get('site_name', 'Rahyaft Sanat')) ?></h1>
        <?php if (!empty($hero['body'])): ?>
            <p class="hero__lead"><?= e($hero['body']) ?></p>
        <?php endif; ?>
        <div class="hero__actions">
            <a class="btn btn--lg btn--accent" href="<?= e(Url::products()) ?>">
                <?= e($hero['cta_label'] ?? __('home.hero.cta_products')) ?>
                <svg class="icon-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                </svg>
            </a>
            <a class="btn btn--lg btn--on-dark-outline" href="<?= e(Url::contact()) ?>">
                <?= _e('home.hero.cta_contact') ?>
            </a>
        </div>
    </div>
</section>

<?php /* --------------------------------------------------------- Introduction */ ?>
<?php if ($intro !== null): ?>
    <section class="section">
        <div class="container">
            <div class="split">
                <?php if (!empty($intro['image_path'])): ?>
                    <div class="split__media reveal">
                        <?= View::partial('partials.image', [
                            'media' => $intro,
                            'sizes' => '(max-width: 720px) 92vw, 46vw',
                            'fallback' => (string) ($intro['heading'] ?? ''),
                        ]) ?>
                    </div>
                <?php endif; ?>

                <div class="reveal">
                    <?php if (!empty($intro['subheading'])): ?>
                        <p class="eyebrow"><?= e($intro['subheading']) ?></p>
                    <?php endif; ?>
                    <h2><?= e($intro['heading'] ?? '') ?></h2>
                    <div class="prose mt-5">
                        <?php foreach (Page::lines($intro['body'] ?? '') as $paragraph): ?>
                            <p><?= e($paragraph) ?></p>
                        <?php endforeach; ?>
                    </div>
                    <?php if (!empty($intro['cta_label'])): ?>
                        <a class="btn btn--outline mt-6" href="<?= e($intro['cta_url'] ?: Url::about()) ?>">
                            <?= e($intro['cta_label']) ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php /* ------------------------------------------------------------ Categories */ ?>
<?php if ($categories !== []): ?>
    <section class="section section--subtle">
        <div class="container">
            <div class="section-head section-head--center">
                <p class="eyebrow"><?= _e('nav.products') ?></p>
                <h2><?= _e('home.categories.title') ?></h2>
                <p><?= _e('home.categories.lead') ?></p>
            </div>

            <div class="category-grid">
                <?php foreach ($categories as $category): ?>
                    <a class="category-card reveal" href="<?= e(Url::category((string) $category['slug'])) ?>">
                        <?php if (!empty($category['image_path'])): ?>
                            <?= View::partial('partials.image', [
                                'media' => $category,
                                'sizes' => '(max-width: 700px) 92vw, 33vw',
                                'fallback' => (string) $category['name'],
                            ]) ?>
                        <?php endif; ?>
                        <h3 class="category-card__title"><?= e($category['name']) ?></h3>
                        <p class="category-card__meta">
                            <span class="ltr-num"><?= e(num((int) $category['product_count'])) ?></span>
                            <span><?= _e('home.products_count') ?></span>
                        </p>
                        <span class="category-card__cta">
                            <?= _e('home.explore') ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                                 stroke-linecap="round" stroke-linejoin="round" width="17" height="17" aria-hidden="true">
                                <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                            </svg>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php /* ------------------------------------------------------ Featured products */ ?>
<?php if ($featured !== []): ?>
    <section class="section">
        <div class="container">
            <div class="section-head">
                <p class="eyebrow"><?= _e('home.featured.title') ?></p>
                <h2><?= _e('home.featured.lead') ?></h2>
            </div>

            <div class="product-grid">
                <?php foreach ($featured as $product): ?>
                    <?= View::partial('partials.product-card', ['product' => $product]) ?>
                <?php endforeach; ?>
            </div>

            <p class="text-center mt-8">
                <a class="btn btn--outline btn--lg" href="<?= e(Url::products()) ?>">
                    <?= _e('common.all_products') ?>
                </a>
            </p>
        </div>
    </section>
<?php endif; ?>

<?php /* --------------------------------------------------------- Capabilities */ ?>
<?php if ($capabilities !== null): ?>
    <section class="section section--brand">
        <div class="container">
            <div class="section-head section-head--center">
                <p class="eyebrow"><?= e($capabilities['subheading'] ?? __('home.capabilities.title')) ?></p>
                <h2><?= e($capabilities['heading'] ?? __('home.capabilities.title')) ?></h2>
            </div>

            <div class="feature-grid">
                <?php foreach (Page::lines($capabilities['body'] ?? '') as $index => $line): ?>
                    <?php
                    // Each line is "Title | description"; the description is optional.
                    [$featureTitle, $featureText] = array_pad(explode('|', $line, 2), 2, '');
                    ?>
                    <div class="feature reveal">
                        <span class="feature__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <?= $capabilityIcons[$index % count($capabilityIcons)] ?>
                            </svg>
                        </span>
                        <h3><?= e(trim($featureTitle)) ?></h3>
                        <?php if (trim($featureText) !== ''): ?>
                            <p><?= e(trim($featureText)) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($stats !== null): ?>
                <div class="stats mt-8">
                    <?php foreach (Page::lines($stats['body'] ?? '') as $line): ?>
                        <?php [$value, $label] = array_pad(explode('|', $line, 2), 2, ''); ?>
                        <div class="stat reveal">
                            <div class="stat__value ltr-num"><?= e(num(trim($value))) ?></div>
                            <div class="stat__label"><?= e(trim($label)) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>

<?php /* ---------------------------------------------------- Research & development */ ?>
<?php if ($research !== []): ?>
    <section class="section">
        <div class="container">
            <div class="section-head">
                <p class="eyebrow"><?= _e('nav.research') ?></p>
                <h2><?= _e('home.research.title') ?></h2>
                <p><?= _e('home.research.lead') ?></p>
            </div>

            <div class="product-grid">
                <?php foreach ($research as $project): ?>
                    <article class="card reveal">
                        <a class="card__media card__media--cover" href="<?= e(Url::researchProject((string) $project['slug'])) ?>"
                           tabindex="-1" aria-hidden="true">
                            <?php if (!empty($project['image_path'])): ?>
                                <?= View::partial('partials.image', [
                                    'media' => $project,
                                    'sizes' => '(max-width: 600px) 92vw, 33vw',
                                    'fallback' => (string) $project['title'],
                                ]) ?>
                            <?php endif; ?>
                        </a>
                        <div class="card__body">
                            <h3 class="card__title">
                                <a href="<?= e(Url::researchProject((string) $project['slug'])) ?>"><?= e($project['title']) ?></a>
                            </h3>
                            <?php if (!empty($project['summary'])): ?>
                                <p class="card__text"><?= e(excerpt((string) $project['summary'], 140)) ?></p>
                            <?php endif; ?>
                            <p class="card__foot">
                                <span><?= _e('common.read_more') ?></span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                                     stroke-linecap="round" stroke-linejoin="round" width="17" height="17" aria-hidden="true">
                                    <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                                </svg>
                            </p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <p class="text-center mt-8">
                <a class="btn btn--outline btn--lg" href="<?= e(Url::research()) ?>"><?= _e('common.view_all') ?></a>
            </p>
        </div>
    </section>
<?php endif; ?>

<?php /* --------------------------------------------------------------- Motto */ ?>
<?php if ($quote !== null && !empty($quote['body'])): ?>
    <section class="section section--tight section--subtle">
        <div class="container">
            <figure class="quote reveal">
                <blockquote class="quote__text"><?= nl2br(e($quote['body'])) ?></blockquote>
                <?php if (!empty($quote['heading'])): ?>
                    <figcaption class="quote__cite">— <?= e($quote['heading']) ?></figcaption>
                <?php endif; ?>
            </figure>
        </div>
    </section>
<?php endif; ?>

<?php /* ------------------------------------------------------------ CTA band */ ?>
<section class="section section--tight">
    <div class="container">
        <div class="cta-band reveal">
            <div class="container cta-band__inner">
                <div>
                    <h2><?= e($cta['heading'] ?? __('products.inquiry_title')) ?></h2>
                    <p><?= e($cta['body'] ?? __('products.inquiry_lead')) ?></p>
                </div>
                <div class="cta-band__actions">
                    <a class="btn btn--lg btn--on-dark" href="<?= e($cta['cta_url'] ?? '') ?: e(Url::contact()) ?>">
                        <?= e($cta['cta_label'] ?? __('contact.send')) ?>
                    </a>
                    <?php if (($phone = Setting::phones()[0] ?? '') !== ''): ?>
                        <a class="btn btn--lg btn--on-dark-outline" href="tel:<?= e(preg_replace('/[^0-9+]/', '', $phone)) ?>">
                            <span class="ltr-num"><?= e(num($phone)) ?></span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
