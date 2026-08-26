<?php
/**
 * About page — renders the CMS section stack.
 *
 * @var array $page
 */

use App\Core\Url;
use App\Core\View;
use App\Models\Page;
use App\Models\Model;

$sections = $page['sections'] ?? [];
?>
<section class="page-hero">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumbs">
                <li><a href="<?= e(Url::home()) ?>"><?= _e('common.home') ?></a></li>
                <li><span aria-current="page"><?= _e('nav.about') ?></span></li>
            </ol>
        </nav>
        <h1><?= e($page['title'] ?? __('about.title')) ?></h1>
        <?php if (!empty($page['subtitle'])): ?>
            <p class="page-hero__lead"><?= e($page['subtitle']) ?></p>
        <?php endif; ?>
    </div>
</section>

<?php if (!empty($page['body'])): ?>
    <section class="section">
        <div class="container container--narrow">
            <div class="prose">
                <?php foreach (Model::lines($page['body']) as $paragraph): ?>
                    <p><?= e($paragraph) ?></p>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php $alternate = 0; ?>
<?php foreach ($sections as $section): ?>
    <?php switch ($section['type']):
        case 'image_text': $alternate++; ?>
            <section class="section<?= $alternate % 2 === 0 ? ' section--subtle' : '' ?>">
                <div class="container">
                    <div class="split<?= $alternate % 2 === 0 ? ' split--reverse' : '' ?>">
                        <?php if (!empty($section['image_path'])): ?>
                            <div class="split__media reveal">
                                <?= View::partial('partials.image', [
                                    'media'    => $section,
                                    'sizes'    => '(max-width: 720px) 92vw, 46vw',
                                    'fallback' => (string) ($section['heading'] ?? ''),
                                ]) ?>
                            </div>
                        <?php endif; ?>
                        <div class="reveal">
                            <?php if (!empty($section['subheading'])): ?>
                                <p class="eyebrow"><?= e($section['subheading']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($section['heading'])): ?>
                                <h2><?= e($section['heading']) ?></h2>
                            <?php endif; ?>
                            <div class="prose mt-5">
                                <?php foreach (Model::lines($section['body'] ?? '') as $paragraph): ?>
                                    <p><?= e($paragraph) ?></p>
                                <?php endforeach; ?>
                            </div>
                            <?php if (!empty($section['cta_label']) && !empty($section['cta_url'])): ?>
                                <a class="btn btn--outline mt-6" href="<?= e($section['cta_url']) ?>">
                                    <?= e($section['cta_label']) ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>
            <?php break; ?>

        <?php case 'features': ?>
            <section class="section section--brand">
                <div class="container">
                    <div class="section-head section-head--center">
                        <?php if (!empty($section['subheading'])): ?>
                            <p class="eyebrow"><?= e($section['subheading']) ?></p>
                        <?php endif; ?>
                        <h2><?= e($section['heading'] ?? '') ?></h2>
                    </div>
                    <div class="feature-grid">
                        <?php foreach (Model::lines($section['body'] ?? '') as $line): ?>
                            <?php [$title, $text] = array_pad(explode('|', $line, 2), 2, ''); ?>
                            <div class="feature reveal">
                                <span class="feature__icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M20 6 9 17l-5-5"/>
                                    </svg>
                                </span>
                                <h3><?= e(trim($title)) ?></h3>
                                <?php if (trim($text) !== ''): ?><p><?= e(trim($text)) ?></p><?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
            <?php break; ?>

        <?php case 'stats': ?>
            <section class="section section--tight section--subtle">
                <div class="container">
                    <?php if (!empty($section['heading'])): ?>
                        <div class="section-head section-head--center"><h2><?= e($section['heading']) ?></h2></div>
                    <?php endif; ?>
                    <div class="stats">
                        <?php foreach (Model::lines($section['body'] ?? '') as $line): ?>
                            <?php [$value, $label] = array_pad(explode('|', $line, 2), 2, ''); ?>
                            <div class="stat reveal">
                                <div class="stat__value ltr-num"><?= e(num(trim($value))) ?></div>
                                <div class="stat__label"><?= e(trim($label)) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
            <?php break; ?>

        <?php case 'richtext': ?>
            <section class="section">
                <div class="container container--narrow">
                    <?php if (!empty($section['heading'])): ?>
                        <div class="section-head"><h2><?= e($section['heading']) ?></h2></div>
                    <?php endif; ?>
                    <div class="prose">
                        <?php foreach (Model::lines($section['body'] ?? '') as $paragraph): ?>
                            <p><?= e($paragraph) ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
            <?php break; ?>

        <?php case 'quote': ?>
            <section class="section section--tight">
                <div class="container">
                    <figure class="quote reveal">
                        <blockquote class="quote__text"><?= nl2br(e($section['body'] ?? '')) ?></blockquote>
                        <?php if (!empty($section['heading'])): ?>
                            <figcaption class="quote__cite">— <?= e($section['heading']) ?></figcaption>
                        <?php endif; ?>
                    </figure>
                </div>
            </section>
            <?php break; ?>

        <?php case 'cta': ?>
            <section class="section section--tight">
                <div class="container">
                    <div class="cta-band reveal">
                        <div class="container cta-band__inner">
                            <div>
                                <h2><?= e($section['heading'] ?? '') ?></h2>
                                <?php if (!empty($section['body'])): ?><p><?= e($section['body']) ?></p><?php endif; ?>
                            </div>
                            <div class="cta-band__actions">
                                <a class="btn btn--lg btn--on-dark" href="<?= e($section['cta_url'] ?: Url::contact()) ?>">
                                    <?= e($section['cta_label'] ?: __('contact.title')) ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <?php break; ?>
    <?php endswitch; ?>
<?php endforeach; ?>
