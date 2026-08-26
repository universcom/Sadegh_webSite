<?php
/** @var array $pages */

use App\Core\Lang;
use App\Core\Url;

$default = Lang::default();

$publicUrl = static fn (string $slug): string => match ($slug) {
    'home'     => Url::home(),
    'about'    => Url::about(),
    'contact'  => Url::contact(),
    'research' => Url::research(),
    default    => Url::home(),
};
?>
<div class="panel">
    <div class="panel__head">
        <h2>Pages</h2>
        <p>Editorial content for the main pages. Products and R&amp;D projects are managed in their own sections.</p>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Page</th><th>Sections</th><th>Last updated</th><th class="right">Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($pages as $page): ?>
                    <tr>
                        <td>
                            <a class="title" href="<?= e(Url::admin('pages/' . $page['id'])) ?>">
                                <?= e($page['translations'][$default]['title'] ?? ucfirst((string) $page['slug'])) ?>
                            </a>
                            <div class="sub ltr">/<?= e($page['slug']) ?></div>
                        </td>
                        <td class="num"><?= e((string) $page['section_count']) ?></td>
                        <td class="num sub nowrap"><?= e(date('Y-m-d H:i', strtotime((string) $page['updated_at']))) ?></td>
                        <td class="actions">
                            <div class="btn-row" style="justify-content:flex-end">
                                <a class="btn ghost sm" href="<?= e($publicUrl((string) $page['slug'])) ?>" target="_blank" rel="noopener">View</a>
                                <a class="btn ghost sm" href="<?= e(Url::admin('pages/' . $page['id'])) ?>">Edit</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
