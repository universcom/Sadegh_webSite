<?php
/**
 * @var array  $result
 * @var string $kind
 * @var string $search
 */

use App\Core\Csrf;
use App\Core\Lang;
use App\Core\Url;
use App\Models\Media;

$items   = $result['items'];
$locales = Lang::enabled();

$query = static function (array $overrides) use ($kind, $search): string {
    $params = array_filter([
        'kind' => $overrides['kind'] ?? $kind,
        'q'    => $overrides['q']    ?? $search,
        'page' => $overrides['page'] ?? null,
    ], static fn ($v) => $v !== '' && $v !== null);

    return Url::admin('media') . ($params === [] ? '' : '?' . http_build_query($params));
};
?>
<div class="panel">
    <div class="panel__head">
        <h2>Upload</h2>
        <p>Images are resized into responsive versions automatically. Uploads are stored with randomised
           filenames and cannot be executed by the server.</p>
    </div>
    <div class="panel__body">
        <form method="post" enctype="multipart/form-data" action="<?= e(Url::admin('media/upload')) ?>">
            <?= Csrf::field() ?>
            <div class="field">
                <label for="file">Choose a file <span class="hint">— JPG, PNG, GIF, WebP (max 8 MB) or PDF, DOC, XLS, ZIP (max 16 MB)</span></label>
                <input class="input" type="file" id="file" name="file" required
                       accept="image/jpeg,image/png,image/gif,image/webp,.pdf,.doc,.docx,.xls,.xlsx,.zip">
            </div>
            <div class="grid-3">
                <?php foreach ($locales as $locale): ?>
                    <div class="field">
                        <label for="malt_<?= e($locale) ?>">Alt text (<?= e(strtoupper($locale)) ?>) <span class="hint">— images only</span></label>
                        <input class="input" type="text" id="malt_<?= e($locale) ?>" name="alt_<?= e($locale) ?>"
                               dir="<?= e(Lang::direction($locale)) ?>" maxlength="255">
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="btn primary" type="submit">Upload</button>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel__head">
        <h2>Library</h2>
        <span class="spacer"></span>
        <span class="muted small"><?= e((string) $result['total']) ?> file(s)</span>
    </div>

    <div class="panel__body">
        <div class="tabs mb-4">
            <a class="tab" href="<?= e($query(['kind' => '', 'page' => null])) ?>" <?= $kind === '' ? 'aria-current="true"' : '' ?>>All</a>
            <a class="tab" href="<?= e($query(['kind' => 'image', 'page' => null])) ?>" <?= $kind === 'image' ? 'aria-current="true"' : '' ?>>Images</a>
            <a class="tab" href="<?= e($query(['kind' => 'document', 'page' => null])) ?>" <?= $kind === 'document' ? 'aria-current="true"' : '' ?>>Documents</a>
        </div>

        <form class="filters mb-4" method="get" action="<?= e(Url::admin('media')) ?>">
            <?php if ($kind !== ''): ?><input type="hidden" name="kind" value="<?= e($kind) ?>"><?php endif; ?>
            <div class="field grow">
                <label for="mq">Search</label>
                <input class="input" type="search" id="mq" name="q" value="<?= e($search) ?>" placeholder="Filename or alt text">
            </div>
            <button class="btn ghost" type="submit">Search</button>
        </form>

        <?php if ($items === []): ?>
            <div class="empty">
                <h3>Nothing here yet</h3>
                <p>Upload an image or document above.</p>
            </div>
        <?php else: ?>
            <div class="media-grid">
                <?php foreach ($items as $item): ?>
                    <?php $usage = Media::usageCount((int) $item['id']); ?>
                    <div class="media-item">
                        <div class="media-item__preview">
                            <?php if ($item['kind'] === 'image'): ?>
                                <img src="<?= e(Url::upload((string) $item['path'])) ?>"
                                     alt="<?= e(Media::alt($item, '')) ?>" loading="lazy">
                            <?php else: ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="#939aab" stroke-width="1.4"
                                     stroke-linecap="round" stroke-linejoin="round" width="46" height="46" aria-hidden="true">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/>
                                </svg>
                            <?php endif; ?>
                        </div>
                        <div class="media-item__body">
                            <div class="media-item__name" title="<?= e((string) $item['original_name']) ?>">
                                <?= e($item['original_name'] ?: basename((string) $item['path'])) ?>
                            </div>
                            <div class="media-item__meta">
                                <?php if ($item['kind'] === 'image' && $item['width']): ?>
                                    <?= e((string) $item['width']) ?>×<?= e((string) $item['height']) ?>
                                <?php else: ?>
                                    <?= e(strtoupper(pathinfo((string) $item['path'], PATHINFO_EXTENSION))) ?>
                                <?php endif; ?>
                                <?php if ($usage > 0): ?>
                                    · <span class="badge blue" style="font-size:.66rem">used ×<?= $usage ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="media-item__actions">
                            <a class="btn ghost sm" href="<?= e(Url::upload((string) $item['path'])) ?>" target="_blank" rel="noopener">Open</a>
                            <button class="btn ghost sm" type="button"
                                    data-copy="<?= e(Url::upload((string) $item['path'])) ?>" title="Copy URL">URL</button>
                            <?php if ($usage === 0): ?>
                                <form class="inline-form" method="post"
                                      action="<?= e(Url::admin('media/' . $item['id'] . '/delete')) ?>"
                                      data-confirm="Delete this file permanently?">
                                    <?= Csrf::field() ?>
                                    <button class="btn danger sm" type="submit">×</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
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
</div>
