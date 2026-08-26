<?php
/**
 * @var array|null $category
 * @var array      $errors
 * @var array      $old
 */

use App\Core\Csrf;
use App\Core\Lang;
use App\Core\Url;

$isEdit  = $category !== null;
$old     = $old ?? [];
$locales = Lang::enabled();
$default = Lang::default();

$action = $isEdit ? Url::admin('categories/' . $category['id']) : Url::admin('categories/create');

$field = static fn (string $key, string $fallback = ''): string => (string) (
    $old[$key] ?? $category[$key] ?? $fallback
);

$tr = static function (string $locale, string $key) use ($old, $category): string {
    if (isset($old['tr'][$locale][$key])) {
        return (string) $old['tr'][$locale][$key];
    }

    return (string) ($category['translations'][$locale][$key] ?? '');
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
                    <button type="button" class="lang-tab" role="tab" id="ctab-<?= e($locale) ?>"
                            aria-controls="cpanel-<?= e($locale) ?>"
                            aria-selected="<?= $index === 0 ? 'true' : 'false' ?>"
                            tabindex="<?= $index === 0 ? '0' : '-1' ?>"><?= e(Lang::nativeName($locale)) ?></button>
                <?php endforeach; ?>
            </div>

            <?php foreach ($locales as $index => $locale): ?>
                <div class="lang-panel" role="tabpanel" id="cpanel-<?= e($locale) ?>"
                     aria-labelledby="ctab-<?= e($locale) ?>" dir="<?= e(Lang::direction($locale)) ?>"
                     <?= $index === 0 ? '' : 'hidden' ?>>
                    <div class="field<?= isset($errors['name']) && $locale === $default ? ' has-error' : '' ?>">
                        <label for="cname-<?= e($locale) ?>">
                            Name <?php if ($locale === $default): ?><span class="hint">— required</span><?php endif; ?>
                        </label>
                        <input class="input" type="text" id="cname-<?= e($locale) ?>"
                               name="tr[<?= e($locale) ?>][name]" maxlength="190" value="<?= e($tr($locale, 'name')) ?>">
                        <?php if (isset($errors['name']) && $locale === $default): ?>
                            <p class="field-error"><?= e($errors['name']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label for="cdesc-<?= e($locale) ?>">Description</label>
                        <textarea class="textarea" id="cdesc-<?= e($locale) ?>"
                                  name="tr[<?= e($locale) ?>][description]"><?= e($tr($locale, 'description')) ?></textarea>
                    </div>

                    <div class="grid-2">
                        <div class="field">
                            <label for="cseot-<?= e($locale) ?>">SEO title</label>
                            <input class="input" type="text" id="cseot-<?= e($locale) ?>"
                                   name="tr[<?= e($locale) ?>][seo_title]" maxlength="190"
                                   value="<?= e($tr($locale, 'seo_title')) ?>">
                        </div>
                        <div class="field">
                            <label for="cseod-<?= e($locale) ?>">SEO description</label>
                            <input class="input" type="text" id="cseod-<?= e($locale) ?>"
                                   name="tr[<?= e($locale) ?>][seo_description]" maxlength="320"
                                   value="<?= e($tr($locale, 'seo_description')) ?>">
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
                <label for="cslug">URL slug <span class="hint">— leave blank to generate one</span></label>
                <input class="input" type="text" id="cslug" name="slug" dir="ltr" maxlength="190"
                       data-slug-from="cname-en" value="<?= e($field('slug')) ?>">
                <?php if (isset($errors['slug'])): ?><p class="field-error"><?= e($errors['slug']) ?></p><?php endif; ?>
            </div>

            <div class="grid-3">
                <div class="field">
                    <label for="csort">Sort order</label>
                    <input class="input" type="number" id="csort" name="sort_order" value="<?= e($field('sort_order', '0')) ?>">
                </div>
                <div class="field">
                    <span class="label">Visibility</span>
                    <label class="checkbox">
                        <input type="checkbox" name="is_active" value="1"
                            <?= ($old === [] ? ($category === null || $category['is_active']) : isset($old['is_active'])) ? 'checked' : '' ?>>
                        <span>Show on the website</span>
                    </label>
                </div>
                <div class="field">
                    <label for="cimage">Category image</label>
                    <input class="input" type="file" id="cimage" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                    <?php if (!empty($category['image']['path'])): ?>
                        <img class="thumb mt-2" style="width:80px;height:80px"
                             src="<?= e(Url::upload((string) $category['image']['path'])) ?>" alt="">
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="panel__foot">
            <button class="btn primary" type="submit"><?= $isEdit ? 'Save category' : 'Create category' ?></button>
            <a class="btn ghost" href="<?= e(Url::admin('categories')) ?>">Cancel</a>
        </div>
    </div>
</form>
