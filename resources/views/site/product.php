<?php
/**
 * Product detail page.
 *
 * @var array $product
 * @var array $related
 */

use App\Core\Url;
use App\Core\View;
use App\Models\Media;
use App\Models\Model;
use App\Models\Setting;

$images = $product['images'];

// The cover image leads the gallery; fall back to it when there are no extras.
if ($images === [] && !empty($product['image_path'])) {
    $images = [[
        'path'     => $product['image_path'],
        'basename' => $product['image_basename'] ?? null,
        'variants' => $product['image_variants'] ?? null,
        'width'    => $product['image_width'] ?? null,
        'height'   => $product['image_height'] ?? null,
        'alt_fa'   => $product['alt_fa'] ?? null,
        'alt_en'   => $product['alt_en'] ?? null,
        'alt_ar'   => $product['alt_ar'] ?? null,
    ]];
}

$primary      = $images[0] ?? null;
$applications = Model::lines($product['applications'] ?? '');
$advantages   = Model::lines($product['advantages'] ?? '');
$description  = Model::lines($product['description'] ?? '');
$features     = $product['features'];
$specGroups   = $product['specGroups'];
$downloads    = $product['downloads'];
?>
<section class="page-hero" style="padding-block: var(--space-7) var(--space-6)">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumbs">
                <li><a href="<?= e(Url::home()) ?>"><?= _e('common.home') ?></a></li>
                <li><a href="<?= e(Url::products()) ?>"><?= _e('nav.products') ?></a></li>
                <?php if (!empty($product['category_slug'])): ?>
                    <li>
                        <a href="<?= e(Url::category((string) $product['category_slug'])) ?>">
                            <?= e($product['category_name']) ?>
                        </a>
                    </li>
                <?php endif; ?>
                <li><span aria-current="page"><?= e($product['name']) ?></span></li>
            </ol>
        </nav>
    </div>
</section>

<section class="section section--tight">
    <div class="container">
        <div class="product-layout">
            <?php /* ------------------------------------------------- Gallery */ ?>
            <div class="gallery" data-gallery>
                <div class="gallery__main">
                    <?php if ($primary !== null): ?>
                        <img data-gallery-main
                             src="<?= e(Url::upload((string) $primary['path'])) ?>"
                             <?php $set = media_srcset($primary); ?>
                             <?php if ($set !== ''): ?>srcset="<?= e($set) ?>" sizes="(max-width: 900px) 92vw, 46vw"<?php endif; ?>
                             alt="<?= e(Media::alt($primary, (string) $product['name'])) ?>"
                             width="<?= e((string) ($primary['width'] ?? 800)) ?>"
                             height="<?= e((string) ($primary['height'] ?? 600)) ?>"
                             fetchpriority="high">
                    <?php endif; ?>
                </div>

                <?php if (count($images) > 1): ?>
                    <div class="gallery__thumbs" role="group" aria-label="<?= _e('products.gallery') ?>">
                        <?php foreach ($images as $index => $image): ?>
                            <button type="button" class="gallery__thumb" data-gallery-thumb
                                    aria-current="<?= $index === 0 ? 'true' : 'false' ?>"
                                    data-full="<?= e(Url::upload((string) $image['path'])) ?>"
                                    data-srcset="<?= e(media_srcset($image)) ?>"
                                    data-alt="<?= e(Media::alt($image, (string) $product['name'])) ?>"
                                    aria-label="<?= e(__('products.gallery') . ' ' . num($index + 1)) ?>">
                                <?= View::partial('partials.image', [
                                    'media'    => $image,
                                    'sizes'    => '90px',
                                    'fallback' => '',
                                ]) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php /* ------------------------------------------------- Summary */ ?>
            <div class="product-summary">
                <?php if (!empty($product['category_name'])): ?>
                    <p class="product-summary__eyebrow"><?= e($product['category_name']) ?></p>
                <?php endif; ?>

                <h1><?= e($product['name']) ?></h1>

                <?php if (!empty($product['model_code'])): ?>
                    <p class="product-summary__model">
                        <span><?= _e('products.model') ?></span>
                        <span class="ltr-num"><?= e($product['model_code']) ?></span>
                    </p>
                <?php endif; ?>

                <?php if (!empty($product['summary'])): ?>
                    <p class="product-summary__lead"><?= e($product['summary']) ?></p>
                <?php endif; ?>

                <?php if ($features !== []): ?>
                    <ul class="checklist mt-6">
                        <?php foreach ($features as $feature): ?>
                            <li><?= e($feature) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <div class="product-summary__actions">
                    <a class="btn btn--accent btn--lg"
                       href="<?= e(Url::contact() . '?product=' . rawurlencode((string) $product['slug'])) ?>">
                        <?= _e('products.inquiry_cta') ?>
                    </a>
                    <?php if (($phone = Setting::phones()[0] ?? '') !== ''): ?>
                        <a class="btn btn--outline btn--lg" href="tel:<?= e(preg_replace('/[^0-9+]/', '', $phone)) ?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"
                                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.2a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/>
                            </svg>
                            <span class="ltr-num"><?= e(num($phone)) ?></span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php /* ---------------------------------------------------------- Detail body */ ?>
<section class="section section--subtle">
    <div class="container">
        <?php if ($description !== []): ?>
            <div class="detail-section">
                <h2 class="detail-section__title"><?= _e('products.description') ?></h2>
                <div class="prose">
                    <?php foreach ($description as $paragraph): ?>
                        <p><?= e($paragraph) ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($specGroups !== []): ?>
            <div class="detail-section">
                <h2 class="detail-section__title"><?= _e('products.specifications') ?></h2>

                <?php foreach ($specGroups as $group): ?>
                    <div class="spec-group">
                        <?php if (!empty($group['title'])): ?>
                            <h3 class="spec-group__title"><?= e($group['title']) ?></h3>
                        <?php endif; ?>

                        <div class="spec-table-wrap">
                            <table class="spec-table">
                                <caption class="visually-hidden">
                                    <?= e($product['name'] . ' — ' . __('products.specifications')) ?>
                                </caption>
                                <tbody>
                                    <?php foreach ($group['rows'] as $row): ?>
                                        <tr>
                                            <th scope="row"><?= e($row['label']) ?></th>
                                            <td class="spec-value"><?= e($row['value']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>

                <p class="spec-note"><?= _e('products.spec_note') ?></p>
            </div>
        <?php endif; ?>

        <?php if ($applications !== [] || $advantages !== []): ?>
            <div class="detail-section">
                <div class="split" style="align-items:start">
                    <?php if ($applications !== []): ?>
                        <div>
                            <h2 class="detail-section__title"><?= _e('products.applications') ?></h2>
                            <ul class="checklist">
                                <?php foreach ($applications as $line): ?>
                                    <li><?= e($line) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ($advantages !== []): ?>
                        <div>
                            <h2 class="detail-section__title"><?= _e('products.advantages') ?></h2>
                            <ul class="checklist">
                                <?php foreach ($advantages as $line): ?>
                                    <li><?= e($line) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($downloads !== []): ?>
            <div class="detail-section">
                <h2 class="detail-section__title"><?= _e('products.downloads') ?></h2>
                <div class="download-list">
                    <?php foreach ($downloads as $download): ?>
                        <a class="download" href="<?= e(Url::upload((string) $download['path'])) ?>"
                           target="_blank" rel="noopener">
                            <span class="download__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                     stroke-linecap="round" stroke-linejoin="round" width="21" height="21" aria-hidden="true">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <path d="M14 2v6h6"/>
                                </svg>
                            </span>
                            <span class="download__text">
                                <span class="download__title"><?= e($download['title']) ?></span>
                                <span class="download__meta ltr-num">
                                    <?= e(strtoupper(pathinfo((string) $download['path'], PATHINFO_EXTENSION))) ?>
                                    · <?= e(Media::humanSize((int) $download['size'])) ?>
                                </span>
                            </span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round" width="19" height="19" aria-hidden="true">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <path d="M7 10l5 5 5-5"/><path d="M12 15V3"/>
                            </svg>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php /* -------------------------------------------------------------- Enquiry */ ?>
<section class="section section--tight">
    <div class="container">
        <div class="cta-band">
            <div class="container cta-band__inner">
                <div>
                    <h2><?= _e('products.inquiry_title') ?></h2>
                    <p><?= _e('products.inquiry_lead') ?></p>
                </div>
                <div class="cta-band__actions">
                    <a class="btn btn--lg btn--on-dark"
                       href="<?= e(Url::contact() . '?product=' . rawurlencode((string) $product['slug'])) ?>">
                        <?= _e('contact.send') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php /* ----------------------------------------------------- Related products */ ?>
<?php if ($related !== []): ?>
    <section class="section section--subtle">
        <div class="container">
            <div class="section-head">
                <h2><?= _e('products.related') ?></h2>
            </div>
            <div class="product-grid">
                <?php foreach ($related as $item): ?>
                    <?= View::partial('partials.product-card', ['product' => $item]) ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
