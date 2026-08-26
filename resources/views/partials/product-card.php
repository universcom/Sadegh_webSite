<?php
/**
 * Product card used on the home page, listings and related-product rails.
 *
 * @var array $product
 */

use App\Core\Url;
use App\Core\View;

$hasImage = (string) ($product['image_path'] ?? '') !== '';
$url      = Url::product((string) $product['slug']);
?>
<article class="card reveal">
    <a class="card__media" href="<?= e($url) ?>" tabindex="-1" aria-hidden="true">
        <?php if ($hasImage): ?>
            <?= View::partial('partials.image', [
                'media'    => $product,
                'sizes'    => '(max-width: 600px) 92vw, (max-width: 1000px) 46vw, 290px',
                'fallback' => (string) $product['name'],
            ]) ?>
        <?php endif; ?>
        <?php if (!empty($product['is_featured'])): ?>
            <span class="card__badge"><?= _e('common.featured') ?></span>
        <?php endif; ?>
    </a>

    <div class="card__body">
        <?php if (!empty($product['model_code'])): ?>
            <p class="card__eyebrow ltr-num"><?= e(num((string) $product['model_code'])) ?></p>
        <?php endif; ?>

        <h3 class="card__title"><a href="<?= e($url) ?>"><?= e($product['name']) ?></a></h3>

        <?php if (!empty($product['summary'])): ?>
            <p class="card__text"><?= e(excerpt((string) $product['summary'], 130)) ?></p>
        <?php endif; ?>

        <p class="card__foot">
            <span><?= _e('common.view_details') ?></span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                 stroke-linecap="round" stroke-linejoin="round" width="17" height="17" aria-hidden="true">
                <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
            </svg>
        </p>
    </div>
</article>
