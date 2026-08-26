<?php
/** @var array $projects */

use App\Core\Csrf;
use App\Core\Lang;
use App\Core\Url;

$default = Lang::default();
?>
<div class="panel">
    <div class="panel__head">
        <h2>Research &amp; development projects</h2>
        <span class="spacer"></span>
        <a class="btn primary" href="<?= e(Url::admin('research/create')) ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            New project
        </a>
    </div>

    <?php if ($projects === []): ?>
        <div class="empty">
            <h3>No projects yet</h3>
            <p>R&amp;D projects appear on the Research &amp; Development page.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr><th style="width:64px"></th><th>Project</th><th>Order</th><th>Status</th><th class="right">Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $project): ?>
                        <tr>
                            <td>
                                <?php if (!empty($project['image_path'])): ?>
                                    <img class="thumb" src="<?= e(Url::upload((string) $project['image_path'])) ?>" alt="" loading="lazy">
                                <?php endif; ?>
                            </td>
                            <td>
                                <a class="title" href="<?= e(Url::admin('research/' . $project['id'])) ?>">
                                    <?= e($project['translations'][$default]['title'] ?? $project['slug']) ?>
                                </a>
                                <div class="sub ltr"><?= e($project['slug']) ?></div>
                            </td>
                            <td class="num sub"><?= e((string) $project['sort_order']) ?></td>
                            <td>
                                <span class="badge <?= $project['status'] === 'published' ? 'green' : 'amber' ?>">
                                    <?= e(ucfirst((string) $project['status'])) ?>
                                </span>
                            </td>
                            <td class="actions">
                                <div class="btn-row" style="justify-content:flex-end">
                                    <a class="btn ghost sm" href="<?= e(Url::researchProject((string) $project['slug'])) ?>" target="_blank" rel="noopener">View</a>
                                    <a class="btn ghost sm" href="<?= e(Url::admin('research/' . $project['id'])) ?>">Edit</a>
                                    <form class="inline-form" method="post"
                                          action="<?= e(Url::admin('research/' . $project['id'] . '/delete')) ?>"
                                          data-confirm="Delete this project permanently?">
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
