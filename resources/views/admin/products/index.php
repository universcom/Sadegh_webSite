<?php
/**
 * Product list with status tabs, category filter and search.
 *
 * @var array $result
 * @var array $filters
 * @var array $categories
 * @var array $counts
 */

use App\Core\Csrf;
use App\Core\Url;

$items  = $result['items'];
$status = $filters['status'];

$tabs = [
    ''          => ['label' => 'All',       'count' => $counts['all']],
    'published' => ['label' => 'Published', 'count' => $counts['published']],
    'draft'     => ['label' => 'Draft',     'count' => $counts['draft']],
    'archived'  => ['label' => 'Archived',  'count' => $counts['archived']],
];

$query = static function (array $overrides) use ($filters): string {
    $params = array_filter([
        'status'   => $overrides['status']   ?? $filters['status'],
        'category' => $overrides['category'] ?? $filters['category'],
        'q'        => $overrides['q']        ?? $filters['search'],
        'page'     => $overrides['page']     ?? null,
    ], static fn ($v) => $v !== '' && $v !== null);

    return Url::admin('products') . ($params === [] ? '' : '?' . http_build_query($params));
};
?>
<div class="panel">
    <div class="panel__head">
        <h2>Products</h2>
        <span class="spacer"></span>
        <a class="btn primary" href="<?= e(Url::admin('products/create')) ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            New product
        </a>
    </div>

    <div class="panel__body">
        <div class="tabs mb-4">
            <?php foreach ($tabs as $key => $tab): ?>
                <a class="tab" href="<?= e($query(['status' => $key, 'page' => null])) ?>"
                   <?= $status === $key ? 'aria-current="true"' : '' ?>>
                    <?= e($tab['label']) ?> <span class="count"><?= e((string) $tab['count']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <form class="filters" method="get" action="<?= e(Url::admin('products')) ?>">
            <?php if ($status !== ''): ?>
                <input type="hidden" name="status" value="<?= e($status) ?>">
            <?php endif; ?>

            <div class="field grow">
                <label for="q">Search</label>
                <input class="input" type="search" id="q" name="q" value="<?= e($filters['search']) ?>"
                       placeholder="Name, slug or model code">
            </div>

            <div class="field">
                <label for="category">Category</label>
                <select class="select" id="category" name="category" data-auto-submit>
                    <option value="">All categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= e((string) $category['id']) ?>"
                            <?= (string) $filters['category'] === (string) $category['id'] ? 'selected' : '' ?>>
                            <?= e($category['translations'][App\Core\Lang::default()]['name'] ?? $category['slug']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button class="btn ghost" type="submit">Filter</button>

            <?php if ($filters['search'] !== '' || $filters['category'] !== ''): ?>
                <a class="btn ghost" href="<?= e(Url::admin('products')) ?>">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if ($items === []): ?>
        <div class="empty">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M12 2 3 7v10l9 5 9-5V7z"/><path d="m3 7 9 5 9-5"/><path d="M12 22V12"/>
            </svg>
            <h3>No products match</h3>
            <p>Try a different filter, or create a new product.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:64px"></th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Model</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th class="right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $product): ?>
                        <tr>
                            <td>
                                <?php if (!empty($product['image_path'])): ?>
                                    <img class="thumb" src="<?= e(Url::upload((string) $product['image_path'])) ?>" alt="" loading="lazy">
                                <?php endif; ?>
                            </td>
                            <td>
                                <a class="title" href="<?= e(Url::admin('products/' . $product['id'])) ?>">
                                    <?= e($product['name'] ?: $product['slug']) ?>
                                </a>
                                <div class="sub ltr"><?= e($product['slug']) ?></div>
                                <?php if (!empty($product['needs_review'])): ?>
                                    <span class="badge amber">Needs review</span>
                                <?php endif; ?>
                                <?php if (!empty($product['is_featured'])): ?>
                                    <span class="badge blue">Featured</span>
                                <?php endif; ?>
                            </td>
                            <td class="sub"><?= e($product['category_name'] ?? '—') ?></td>
                            <td class="sub ltr num"><?= e($product['model_code'] ?? '—') ?></td>
                            <td class="num sub"><?= e((string) $product['sort_order']) ?></td>
                            <td>
                                <?php
                                $badge = match ($product['status']) {
                                    'published' => 'green',
                                    'draft'     => 'amber',
                                    default     => 'gray',
                                };
                                ?>
                                <span class="badge <?= $badge ?>"><?= e(ucfirst((string) $product['status'])) ?></span>
                            </td>
                            <td class="actions">
                                <div class="btn-row" style="justify-content:flex-end">
                                    <a class="btn ghost sm" href="<?= e(Url::product((string) $product['slug'])) ?>"
                                       target="_blank" rel="noopener" title="View on site">View</a>
                                    <a class="btn ghost sm" href="<?= e(Url::admin('products/' . $product['id'])) ?>">Edit</a>

                                    <form class="inline-form" method="post"
                                          action="<?= e(Url::admin('products/' . $product['id'] . '/status')) ?>">
                                        <?= Csrf::field() ?>
                                        <input type="hidden" name="status"
                                               value="<?= $product['status'] === 'published' ? 'draft' : 'published' ?>">
                                        <button class="btn ghost sm" type="submit">
                                            <?= $product['status'] === 'published' ? 'Unpublish' : 'Publish' ?>
                                        </button>
                                    </form>

                                    <form class="inline-form" method="post"
                                          action="<?= e(Url::admin('products/' . $product['id'] . '/delete')) ?>"
                                          data-confirm="Delete this product permanently? Its images, specifications and documents will be removed too.">
                                        <?= Csrf::field() ?>
                                        <button class="btn danger sm" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($result['pages'] > 1): ?>
            <nav class="pager" aria-label="Pagination">
                <?php for ($page = 1; $page <= $result['pages']; $page++): ?>
                    <a href="<?= e($query(['page' => $page > 1 ? $page : null])) ?>"
                       <?= $page === $result['page'] ? 'aria-current="page"' : '' ?>><?= $page ?></a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>
