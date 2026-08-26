<?php
/** @var array $categories */

use App\Core\Csrf;
use App\Core\Lang;
use App\Core\Url;

$default = Lang::default();
?>
<div class="panel">
    <div class="panel__head">
        <h2>Product categories</h2>
        <span class="spacer"></span>
        <a class="btn primary" href="<?= e(Url::admin('categories/create')) ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            New category
        </a>
    </div>

    <?php if ($categories === []): ?>
        <div class="empty">
            <h3>No categories yet</h3>
            <p>Categories group your products on the site and in the navigation.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:64px"></th>
                        <th>Category</th>
                        <th>Products</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th class="right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td>
                                <?php if (!empty($category['image_path'])): ?>
                                    <img class="thumb" src="<?= e(Url::upload((string) $category['image_path'])) ?>" alt="" loading="lazy">
                                <?php endif; ?>
                            </td>
                            <td>
                                <a class="title" href="<?= e(Url::admin('categories/' . $category['id'])) ?>">
                                    <?= e($category['translations'][$default]['name'] ?? $category['slug']) ?>
                                </a>
                                <div class="sub ltr"><?= e($category['slug']) ?></div>
                            </td>
                            <td class="num"><?= e((string) $category['product_count']) ?></td>
                            <td class="num sub"><?= e((string) $category['sort_order']) ?></td>
                            <td>
                                <span class="badge <?= $category['is_active'] ? 'green' : 'gray' ?>">
                                    <?= $category['is_active'] ? 'Active' : 'Hidden' ?>
                                </span>
                            </td>
                            <td class="actions">
                                <div class="btn-row" style="justify-content:flex-end">
                                    <a class="btn ghost sm" href="<?= e(Url::category((string) $category['slug'])) ?>" target="_blank" rel="noopener">View</a>
                                    <a class="btn ghost sm" href="<?= e(Url::admin('categories/' . $category['id'])) ?>">Edit</a>
                                    <form class="inline-form" method="post"
                                          action="<?= e(Url::admin('categories/' . $category['id'] . '/delete')) ?>"
                                          data-confirm="Delete this category? Its <?= (int) $category['product_count'] ?> product(s) will be kept but left uncategorised.">
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
    <?php endif; ?>
</div>
