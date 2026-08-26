<?php
/**
 * @var array|null $project
 * @var array      $errors
 * @var array      $old
 */

use App\Core\Csrf;
use App\Core\Lang;
use App\Core\Url;
use App\Models\Media;

$isEdit  = $project !== null;
$old     = $old ?? [];
$locales = Lang::enabled();
$default = Lang::default();

$action = $isEdit ? Url::admin('research/' . $project['id']) : Url::admin('research/create');

$field = static fn (string $key, string $fallback = ''): string => (string) ($old[$key] ?? $project[$key] ?? $fallback);

$tr = static function (string $locale, string $key) use ($old, $project): string {
    if (isset($old['tr'][$locale][$key])) {
        return (string) $old['tr'][$locale][$key];
    }

    return (string) ($project['translations'][$locale][$key] ?? '');
};
?>
<?php if ($errors !== []): ?>
    <div class="alert error" role="alert">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/>
        </svg>
        <span><strong>Please correct the following:</strong>
            <?php foreach ($errors as $message): ?><br><?= e($message) ?><?php endforeach; ?>
        </span>
    </div>
<?php endif; ?>

<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data">
    <?= Csrf::field() ?>

    <div class="panel">
        <div class="panel__head">
            <h2>Content</h2>
            <p>Blank fields fall back to <strong><?= e(strtoupper($default)) ?></strong> on the public site.</p>
        </div>
        <div class="panel__body">
            <div class="lang-tabs" role="tablist" data-lang-tabs>
                <?php foreach ($locales as $index => $locale): ?>
                    <button type="button" class="lang-tab" role="tab" id="rtab-<?= e($locale) ?>"
                            aria-controls="rpanel-<?= e($locale) ?>"
                            aria-selected="<?= $index === 0 ? 'true' : 'false' ?>"
                            tabindex="<?= $index === 0 ? '0' : '-1' ?>"><?= e(Lang::nativeName($locale)) ?></button>
                <?php endforeach; ?>
            </div>

            <?php foreach ($locales as $index => $locale): ?>
                <div class="lang-panel" role="tabpanel" id="rpanel-<?= e($locale) ?>"
                     aria-labelledby="rtab-<?= e($locale) ?>" dir="<?= e(Lang::direction($locale)) ?>"
                     <?= $index === 0 ? '' : 'hidden' ?>>
                    <div class="field<?= isset($errors['title']) && $locale === $default ? ' has-error' : '' ?>">
                        <label for="rtitle-<?= e($locale) ?>">
                            Title <?php if ($locale === $default): ?><span class="hint">— required</span><?php endif; ?>
                        </label>
                        <input class="input" type="text" id="rtitle-<?= e($locale) ?>"
                               name="tr[<?= e($locale) ?>][title]" maxlength="190" value="<?= e($tr($locale, 'title')) ?>">
                        <?php if (isset($errors['title']) && $locale === $default): ?>
                            <p class="field-error"><?= e($errors['title']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label for="rsum-<?= e($locale) ?>">Summary</label>
                        <textarea class="textarea" id="rsum-<?= e($locale) ?>" style="min-height:80px"
                                  name="tr[<?= e($locale) ?>][summary]" maxlength="500"><?= e($tr($locale, 'summary')) ?></textarea>
                    </div>

                    <div class="field">
                        <label for="rbody-<?= e($locale) ?>">Body <span class="hint">— one paragraph per line</span></label>
                        <textarea class="textarea tall" id="rbody-<?= e($locale) ?>"
                                  name="tr[<?= e($locale) ?>][body]"><?= e($tr($locale, 'body')) ?></textarea>
                    </div>

                    <div class="grid-2">
                        <div class="field">
                            <label for="rseot-<?= e($locale) ?>">SEO title</label>
                            <input class="input" type="text" id="rseot-<?= e($locale) ?>"
                                   name="tr[<?= e($locale) ?>][seo_title]" maxlength="190" value="<?= e($tr($locale, 'seo_title')) ?>">
                        </div>
                        <div class="field">
                            <label for="rseod-<?= e($locale) ?>">SEO description</label>
                            <input class="input" type="text" id="rseod-<?= e($locale) ?>"
                                   name="tr[<?= e($locale) ?>][seo_description]" maxlength="320" value="<?= e($tr($locale, 'seo_description')) ?>">
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="panel">
        <div class="panel__head"><h2>Settings</h2></div>
        <div class="panel__body">
            <div class="field<?= isset($errors['slug']) ? ' has-error' : '' ?>">
                <label for="rslug">URL slug <span class="hint">— leave blank to generate one</span></label>
                <input class="input" type="text" id="rslug" name="slug" dir="ltr" maxlength="190"
                       data-slug-from="rtitle-en" value="<?= e($field('slug')) ?>">
                <?php if (isset($errors['slug'])): ?><p class="field-error"><?= e($errors['slug']) ?></p><?php endif; ?>
            </div>

            <div class="grid-3">
                <div class="field">
                    <label for="rstatus">Status</label>
                    <select class="select" id="rstatus" name="status">
                        <option value="published" <?= $field('status', 'published') === 'published' ? 'selected' : '' ?>>Published</option>
                        <option value="draft" <?= $field('status') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    </select>
                </div>
                <div class="field">
                    <label for="rsort">Sort order</label>
                    <input class="input" type="number" id="rsort" name="sort_order" value="<?= e($field('sort_order', '0')) ?>">
                </div>
                <div class="field">
                    <label for="rcover">Cover image</label>
                    <input class="input" type="file" id="rcover" name="cover" accept="image/jpeg,image/png,image/gif,image/webp">
                </div>
            </div>
        </div>
        <div class="panel__foot">
            <button class="btn primary" type="submit"><?= $isEdit ? 'Save project' : 'Create project' ?></button>
            <a class="btn ghost" href="<?= e(Url::admin('research')) ?>">Cancel</a>
        </div>
    </div>
</form>

<?php if ($isEdit): ?>
    <div class="panel">
        <div class="panel__head">
            <h2>Project gallery</h2>
            <p>Images shown on the project page. The first uploaded image also becomes the cover.</p>
        </div>
        <div class="panel__body">
            <?php if ($project['images'] === []): ?>
                <p class="muted small mb-4">No images yet.</p>
            <?php else: ?>
                <div class="gallery-editor mb-4">
                    <?php foreach ($project['images'] as $image): ?>
                        <figure>
                            <img src="<?= e(Url::upload((string) $image['path'])) ?>" alt="<?= e(Media::alt($image, '')) ?>" loading="lazy">
                            <figcaption>
                                <form method="post"
                                      action="<?= e(Url::admin('research/' . $project['id'] . '/images/' . $image['id'] . '/delete')) ?>"
                                      data-confirm="Remove this image from the project?">
                                    <?= Csrf::field() ?>
                                    <button class="btn ghost sm" type="submit">Remove</button>
                                </form>
                            </figcaption>
                        </figure>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data"
                  action="<?= e(Url::admin('research/' . $project['id'] . '/images')) ?>">
                <?= Csrf::field() ?>
                <div class="field">
                    <label for="rimage">Add an image</label>
                    <input class="input" type="file" id="rimage" name="image" accept="image/jpeg,image/png,image/gif,image/webp" required>
                </div>
                <div class="grid-3">
                    <?php foreach ($locales as $locale): ?>
                        <div class="field">
                            <label for="ralt_<?= e($locale) ?>">Alt text (<?= e(strtoupper($locale)) ?>)</label>
                            <input class="input" type="text" id="ralt_<?= e($locale) ?>" name="alt_<?= e($locale) ?>"
                                   dir="<?= e(Lang::direction($locale)) ?>" maxlength="255">
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="btn primary" type="submit">Upload image</button>
            </form>
        </div>
    </div>
<?php endif; ?>
