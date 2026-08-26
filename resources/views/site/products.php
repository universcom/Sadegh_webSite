<?php
/**
 * Product listing with category filter, search and sorting.
 *
 * @var array       $result      items/total/pages/page
 * @var array       $categories
 * @var array|null  $category    active category, when browsing one
 * @var array       $filters
 */

use App\Core\Url;
use App\Core\View;

$items = $result['items'];
?>
<section class="page-hero">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumbs">
                <li><a href="<?= e(Url::home()) ?>"><?= _e('common.home') ?></a></li>
                <?php if ($category !== null): ?>
                    <li><a href="<?= e(Url::products()) ?>"><?= _e('nav.products') ?></a></li>
                    <li><span aria-current="page"><?= e($category['name']) ?></span></li>
                <?php else: ?>
                    <li><span aria-current="page"><?= _e('nav.products') ?></span></li>
                <?php endif; ?>
            </ol>
        </nav>

        <h1><?= e($category['name'] ?? __('products.title')) ?></h1>
        <p class="page-hero__lead">
            <?= e($category['description'] ?? __('products.lead')) ?>
        </p>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php /* Category chips double as the primary filter and as navigation. */ ?>
        <div class="chip-row mb-5" style="margin-block-end: var(--space-6)">
            <a class="chip" href="<?= e(Url::products()) ?>"
               <?= $category === null ? 'aria-current="true"' : '' ?>>
                <?= _e('products.all') ?>
            </a>
            <?php foreach ($categories as $item): ?>
                <a class="chip" href="<?= e(Url::category((string) $item['slug'])) ?>"
                   <?= ($category['slug'] ?? null) === $item['slug'] ? 'aria-current="true"' : '' ?>>
                    <?= e($item['name']) ?>
                    <span class="chip__count"><?= e(num((int) $item['product_count'])) ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <form class="toolbar" method="get" action="<?= e($category !== null ? Url::category((string) $category['slug']) : Url::products()) ?>">
            <div class="field" style="flex:1 1 260px; max-width:340px">
                <label class="visually-hidden" for="product-search"><?= _e('common.search') ?></label>
                <input class="input" type="search" id="product-search" name="q"
                       value="<?= e($filters['search'] ?? '') ?>"
                       placeholder="<?= _e('common.search_placeholder') ?>">
            </div>

            <div class="field" style="flex:0 1 200px">
                <label class="visually-hidden" for="product-sort"><?= _e('common.sort') ?></label>
                <select class="select" id="product-sort" name="sort" data-auto-submit>
                    <option value=""       <?= ($filters['sort'] ?? '') === ''       ? 'selected' : '' ?>><?= _e('products.sort.default') ?></option>
                    <option value="newest" <?= ($filters['sort'] ?? '') === 'newest' ? 'selected' : '' ?>><?= _e('products.sort.newest') ?></option>
                    <option value="name"   <?= ($filters['sort'] ?? '') === 'name'   ? 'selected' : '' ?>><?= _e('products.sort.name') ?></option>
                </select>
            </div>

            <button class="btn btn--primary" type="submit"><?= _e('common.search') ?></button>

            <?php if (($filters['search'] ?? '') !== '' || ($filters['sort'] ?? '') !== ''): ?>
                <a class="btn btn--ghost" href="<?= e($category !== null ? Url::category((string) $category['slug']) : Url::products()) ?>">
                    <?= _e('common.reset') ?>
                </a>
            <?php endif; ?>

            <p class="toolbar__count toolbar__spacer">
                <?= e(__('products.count', ['count' => num((int) $result['total'])])) ?>
            </p>
        </form>

        <?php if ($items === []): ?>
            <div class="empty-state">
                <span class="empty-state__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                         stroke-linecap="round" stroke-linejoin="round" width="30" height="30" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                    </svg>
                </span>
                <h3><?= _e('products.empty') ?></h3>
                <p><?= _e('products.empty_hint') ?></p>
                <a class="btn btn--primary" href="<?= e(Url::products()) ?>"><?= _e('common.all_products') ?></a>
            </div>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($items as $product): ?>
                    <?= View::partial('partials.product-card', ['product' => $product]) ?>
                <?php endforeach; ?>
            </div>

            <?= View::partial('partials.pagination', [
                'page'    => (int) $result['page'],
                'pages'   => (int) $result['pages'],
                'request' => $request,
            ]) ?>
        <?php endif; ?>
    </div>
</section>
