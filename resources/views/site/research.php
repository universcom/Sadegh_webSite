<?php
/**
 * Research & development index.
 *
 * @var array      $projects
 * @var array|null $page
 */

use App\Core\Url;
use App\Core\View;
use App\Models\Model;
?>
<section class="page-hero">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumbs">
                <li><a href="<?= e(Url::home()) ?>"><?= _e('common.home') ?></a></li>
                <li><span aria-current="page"><?= _e('nav.research') ?></span></li>
            </ol>
        </nav>
        <h1><?= e($page['title'] ?? __('research.title')) ?></h1>
        <p class="page-hero__lead"><?= e($page['subtitle'] ?? __('research.lead')) ?></p>
    </div>
</section>

<?php if (!empty($page['body'])): ?>
    <section class="section section--tight">
        <div class="container container--narrow">
            <div class="prose">
                <?php foreach (Model::lines($page['body']) as $paragraph): ?>
                    <p><?= e($paragraph) ?></p>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<section class="section<?= empty($page['body']) ? '' : ' section--subtle' ?>">
    <div class="container">
        <?php if ($projects === []): ?>
            <div class="empty-state">
                <h3><?= _e('research.empty') ?></h3>
            </div>
        <?php else: ?>
            <div class="section-head">
                <p class="eyebrow"><?= _e('research.projects') ?></p>
                <h2><?= _e('research.title') ?></h2>
            </div>

            <?php /* Alternating image/text rows read better than a grid for a
                     handful of substantial research areas. */ ?>
            <?php foreach ($projects as $index => $project): ?>
                <div class="split reveal<?= $index % 2 === 1 ? ' split--reverse' : '' ?>"
                     style="margin-block-start: <?= $index === 0 ? '0' : 'var(--space-9)' ?>">
                    <?php if (!empty($project['image_path'])): ?>
                        <a class="split__media" href="<?= e(Url::researchProject((string) $project['slug'])) ?>">
                            <?= View::partial('partials.image', [
                                'media'    => $project,
                                'sizes'    => '(max-width: 720px) 92vw, 46vw',
                                'fallback' => (string) $project['title'],
                            ]) ?>
                        </a>
                    <?php endif; ?>

                    <div>
                        <p class="eyebrow"><?= e(num($index + 1)) ?> / <?= e(num(count($projects))) ?></p>
                        <h3 style="font-size: var(--step-3)">
                            <a href="<?= e(Url::researchProject((string) $project['slug'])) ?>"
                               style="color: var(--ink-900)"><?= e($project['title']) ?></a>
                        </h3>
                        <?php if (!empty($project['summary'])): ?>
                            <p class="mt-4 text-muted" style="font-size: var(--step-1); line-height:1.85">
                                <?= e(excerpt((string) $project['summary'], 260)) ?>
                            </p>
                        <?php endif; ?>
                        <a class="btn btn--outline mt-6" href="<?= e(Url::researchProject((string) $project['slug'])) ?>">
                            <?= _e('common.read_more') ?>
                            <svg class="icon-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
