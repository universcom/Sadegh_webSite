<?php
/**
 * Research project detail.
 *
 * @var array $project
 * @var array $siblings
 */

use App\Core\Url;
use App\Core\View;
use App\Models\Model;

$images = $project['images'];
?>
<section class="page-hero">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumbs">
                <li><a href="<?= e(Url::home()) ?>"><?= _e('common.home') ?></a></li>
                <li><a href="<?= e(Url::research()) ?>"><?= _e('nav.research') ?></a></li>
                <li><span aria-current="page"><?= e($project['title']) ?></span></li>
            </ol>
        </nav>
        <h1><?= e($project['title']) ?></h1>
        <?php if (!empty($project['summary'])): ?>
            <p class="page-hero__lead"><?= e($project['summary']) ?></p>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <div class="container container--narrow">
        <?php if (!empty($project['image_path'])): ?>
            <div class="split__media" style="margin-block-end: var(--space-8)">
                <?= View::partial('partials.image', [
                    'media'    => $project,
                    'sizes'    => '(max-width: 900px) 92vw, 860px',
                    'fallback' => (string) $project['title'],
                    'eager'    => true,
                ]) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($project['body'])): ?>
            <div class="prose">
                <?php foreach (Model::lines($project['body']) as $paragraph): ?>
                    <p><?= e($paragraph) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php if (count($images) > 0): ?>
    <section class="section section--subtle section--tight">
        <div class="container">
            <div class="section-head">
                <h2><?= _e('research.gallery') ?></h2>
            </div>
            <div class="product-grid">
                <?php foreach ($images as $image): ?>
                    <figure class="card reveal" style="padding: var(--space-3)">
                        <div style="border-radius: var(--radius-md); overflow:hidden; background: var(--ink-50)">
                            <?= View::partial('partials.image', [
                                'media'    => $image,
                                'sizes'    => '(max-width: 600px) 92vw, 33vw',
                                'fallback' => (string) $project['title'],
                                'class'    => 'research-figure',
                            ]) ?>
                        </div>
                        <?php $caption = \App\Models\Media::alt($image, ''); ?>
                        <?php if ($caption !== ''): ?>
                            <figcaption class="text-muted" style="padding: var(--space-3); font-size: var(--step--1)">
                                <?= e($caption) ?>
                            </figcaption>
                        <?php endif; ?>
                    </figure>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if ($siblings !== []): ?>
    <section class="section">
        <div class="container">
            <div class="section-head">
                <h2><?= _e('research.other') ?></h2>
            </div>
            <div class="product-grid">
                <?php foreach ($siblings as $item): ?>
                    <article class="card reveal">
                        <a class="card__media card__media--cover" href="<?= e(Url::researchProject((string) $item['slug'])) ?>"
                           tabindex="-1" aria-hidden="true">
                            <?php if (!empty($item['image_path'])): ?>
                                <?= View::partial('partials.image', [
                                    'media' => $item, 'sizes' => '(max-width: 600px) 92vw, 33vw',
                                    'fallback' => (string) $item['title'],
                                ]) ?>
                            <?php endif; ?>
                        </a>
                        <div class="card__body">
                            <h3 class="card__title">
                                <a href="<?= e(Url::researchProject((string) $item['slug'])) ?>"><?= e($item['title']) ?></a>
                            </h3>
                            <?php if (!empty($item['summary'])): ?>
                                <p class="card__text"><?= e(excerpt((string) $item['summary'], 120)) ?></p>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
