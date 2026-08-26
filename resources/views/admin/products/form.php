<?php
/**
 * Product editor.
 *
 * Content fields are edited per language through tabs; structural fields
 * (category, slug, status, ordering) are language-neutral. Images and documents
 * are managed only after the product exists, so uploads always have an owner.
 *
 * @var array|null $product
 * @var array      $categories
 * @var array      $errors
 * @var array      $old
 */

use App\Core\Csrf;
use App\Core\Lang;
use App\Core\Url;
use App\Models\Media;

$isEdit  = $product !== null;
$old     = $old ?? [];
$locales = Lang::enabled();
$default = Lang::default();

$action = $isEdit
    ? Url::admin('products/' . $product['id'])
    : Url::admin('products/create');

/** Value of a language-neutral field: resubmitted input → stored row → default. */
$field = static function (string $key, string $fallback = '') use ($old, $product): string {
    if (array_key_exists($key, $old)) {
        return (string) $old[$key];
    }

    return (string) ($product[$key] ?? $fallback);
};

/** Value of a translated field. */
$tr = static function (string $locale, string $key) use ($old, $product): string {
    if (isset($old['tr'][$locale][$key])) {
        return (string) $old['tr'][$locale][$key];
    }

    return (string) ($product['translations'][$locale][$key] ?? '');
};

$checked = static function (string $key) use ($old, $product): bool {
    if ($old !== []) {
        return isset($old[$key]);
    }

    return !empty($product[$key]);
};

// Existing spec groups, or one empty group for a new product.
$specGroups = $product['specs'] ?? [];
if ($specGroups === []) {
    $specGroups = [['titles' => [], 'rows' => [['values' => []]]]];
}

$features = $product['features'] ?? [];
if ($features === []) {
    $features = [['texts' => []]];
}
?>

<?php if ($errors !== []): ?>
    <div class="alert error" role="alert">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/>
        </svg>
        <span>
            <strong>Please correct the following:</strong>
            <?php foreach ($errors as $message): ?><br><?= e($message) ?><?php endforeach; ?>
        </span>
    </div>
<?php endif; ?>

<form method="post" action="<?= e($action) ?>">
    <?= Csrf::field() ?>

    <!-- ================================================= Translated content -->
    <div class="panel">
        <div class="panel__head">
            <h2>Content</h2>
            <p>Each language is stored separately. Any field left blank falls back to
               <strong><?= e(strtoupper($default)) ?></strong> on the public site.</p>
        </div>
        <div class="panel__body">
            <div class="lang-tabs" role="tablist" data-lang-tabs>
                <?php foreach ($locales as $index => $locale): ?>
                    <button type="button" class="lang-tab" role="tab"
                            id="tab-<?= e($locale) ?>" aria-controls="panel-<?= e($locale) ?>"
                            aria-selected="<?= $index === 0 ? 'true' : 'false' ?>"
                            tabindex="<?= $index === 0 ? '0' : '-1' ?>">
                        <?= e(Lang::nativeName($locale)) ?>
                        <?php if ($locale === $default): ?><span class="hint">· default</span><?php endif; ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <?php foreach ($locales as $index => $locale): ?>
                <div class="lang-panel" role="tabpanel" id="panel-<?= e($locale) ?>"
                     aria-labelledby="tab-<?= e($locale) ?>" dir="<?= e(Lang::direction($locale)) ?>"
                     <?= $index === 0 ? '' : 'hidden' ?>>

                    <div class="field<?= isset($errors['name']) && $locale === $default ? ' has-error' : '' ?>">
                        <label for="name-<?= e($locale) ?>">
                            Product name
                            <?php if ($locale === $default): ?><span class="hint">— required</span><?php endif; ?>
                        </label>
                        <input class="input" type="text" id="name-<?= e($locale) ?>"
                               name="tr[<?= e($locale) ?>][name]" maxlength="190"
                               value="<?= e($tr($locale, 'name')) ?>">
                        <?php if (isset($errors['name']) && $locale === $default): ?>
                            <p class="field-error"><?= e($errors['name']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label for="summary-<?= e($locale) ?>">
                            Short summary <span class="hint">— shown on cards and in search results</span>
                        </label>
                        <textarea class="textarea" id="summary-<?= e($locale) ?>"
                                  name="tr[<?= e($locale) ?>][summary]" maxlength="500"
                                  style="min-height:80px"><?= e($tr($locale, 'summary')) ?></textarea>
                    </div>

                    <div class="field">
                        <label for="description-<?= e($locale) ?>">
                            Description <span class="hint">— one paragraph per line</span>
                        </label>
                        <textarea class="textarea tall" id="description-<?= e($locale) ?>"
                                  name="tr[<?= e($locale) ?>][description]"><?= e($tr($locale, 'description')) ?></textarea>
                    </div>

                    <div class="grid-2">
                        <div class="field">
                            <label for="applications-<?= e($locale) ?>">
                                Applications <span class="hint">— one per line</span>
                            </label>
                            <textarea class="textarea" id="applications-<?= e($locale) ?>"
                                      name="tr[<?= e($locale) ?>][applications]"><?= e($tr($locale, 'applications')) ?></textarea>
                        </div>
                        <div class="field">
                            <label for="advantages-<?= e($locale) ?>">
                                Advantages <span class="hint">— one per line</span>
                            </label>
                            <textarea class="textarea" id="advantages-<?= e($locale) ?>"
                                      name="tr[<?= e($locale) ?>][advantages]"><?= e($tr($locale, 'advantages')) ?></textarea>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ============================================================ Settings -->
    <div class="panel">
        <div class="panel__head"><h2>Settings</h2></div>
        <div class="panel__body">
            <div class="grid-2">
                <div class="field">
                    <label for="category_id">Category</label>
                    <select class="select" id="category_id" name="category_id">
                        <option value="">— none —</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= e((string) $category['id']) ?>"
                                <?= (string) $field('category_id') === (string) $category['id'] ? 'selected' : '' ?>>
                                <?= e($category['translations'][$default]['name'] ?? $category['slug']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label for="model_code">Model code <span class="hint">— optional</span></label>
                    <input class="input" type="text" id="model_code" name="model_code" dir="ltr"
                           maxlength="90" value="<?= e($field('model_code')) ?>">
                </div>
            </div>

            <div class="field<?= isset($errors['slug']) ? ' has-error' : '' ?>">
                <label for="slug">
                    URL slug
                    <span class="hint">— lowercase Latin letters, numbers and hyphens. Leave blank to generate one.</span>
                </label>
                <input class="input" type="text" id="slug" name="slug" dir="ltr" maxlength="190"
                       data-slug-from="name-en" value="<?= e($field('slug')) ?>">
                <?php if (isset($errors['slug'])): ?>
                    <p class="field-error"><?= e($errors['slug']) ?></p>
                <?php endif; ?>
                <?php if ($isEdit): ?>
                    <p class="small muted mt-2">
                        Public URL: <span class="ltr"><?= e(Url::product((string) $product['slug'])) ?></span>
                    </p>
                <?php endif; ?>
            </div>

            <div class="grid-3">
                <div class="field">
                    <label for="status">Status</label>
                    <select class="select" id="status" name="status">
                        <?php foreach (['published' => 'Published', 'draft' => 'Draft', 'archived' => 'Archived'] as $value => $label): ?>
                            <option value="<?= e($value) ?>" <?= $field('status', 'published') === $value ? 'selected' : '' ?>>
                                <?= e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label for="sort_order">Sort order <span class="hint">— lower first</span></label>
                    <input class="input" type="number" id="sort_order" name="sort_order"
                           value="<?= e($field('sort_order', '0')) ?>">
                </div>

                <div class="field">
                    <span class="label">Flags</span>
                    <label class="checkbox">
                        <input type="checkbox" name="is_featured" value="1" <?= $checked('is_featured') ? 'checked' : '' ?>>
                        <span>Featured on the home page</span>
                    </label>
                    <label class="checkbox mt-2">
                        <input type="checkbox" name="needs_review" value="1" <?= $checked('needs_review') ? 'checked' : '' ?>>
                        <span>Needs review</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- ====================================================== Specifications -->
    <div class="panel">
        <div class="panel__head">
            <h2>Technical specifications</h2>
            <p>Grouped tables, e.g. Travels / Spindle / Table. Rows left blank are discarded on save.</p>
            <span class="spacer"></span>
            <button class="btn ghost sm" type="button" data-repeat-add="spec-groups">Add group</button>
        </div>
        <div class="panel__body">
            <div id="spec-groups" data-repeat data-repeat-template="spec-group-template">
                <?php foreach ($specGroups as $groupIndex => $group): ?>
                    <div class="repeat-row" data-repeat-row>
                        <div class="repeat-row__head">
                            <span>Group <?= $groupIndex + 1 ?></span>
                            <span class="spacer"></span>
                            <button class="btn ghost sm" type="button" data-repeat-remove>Remove group</button>
                        </div>

                        <div class="grid-3">
                            <?php foreach ($locales as $locale): ?>
                                <div class="field" style="margin-bottom:0">
                                    <label>Group title (<?= e(strtoupper($locale)) ?>)</label>
                                    <input class="input" type="text" dir="<?= e(Lang::direction($locale)) ?>"
                                           name="specs[<?= $groupIndex ?>][title][<?= e($locale) ?>]"
                                           maxlength="190"
                                           value="<?= e($group['titles'][$locale] ?? '') ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div id="spec-rows-<?= $groupIndex ?>" data-repeat
                             data-repeat-template="spec-row-template-<?= $groupIndex ?>" class="mt-3">
                            <?php foreach ($group['rows'] as $rowIndex => $row): ?>
                                <div class="repeat-row" data-repeat-row style="background:#fff">
                                    <?php foreach ($locales as $locale): ?>
                                        <div class="spec-pair" dir="<?= e(Lang::direction($locale)) ?>">
                                            <input class="input" type="text"
                                                   placeholder="Label (<?= e(strtoupper($locale)) ?>)"
                                                   name="specs[<?= $groupIndex ?>][rows][<?= $rowIndex ?>][<?= e($locale) ?>][label]"
                                                   maxlength="190"
                                                   value="<?= e($row['values'][$locale]['label'] ?? '') ?>">
                                            <input class="input" type="text"
                                                   placeholder="Value (<?= e(strtoupper($locale)) ?>)"
                                                   name="specs[<?= $groupIndex ?>][rows][<?= $rowIndex ?>][<?= e($locale) ?>][value]"
                                                   maxlength="500"
                                                   value="<?= e($row['values'][$locale]['value'] ?? '') ?>">
                                            <?php if ($locale === $locales[0]): ?>
                                                <button class="btn ghost sm" type="button" data-repeat-remove title="Remove row">×</button>
                                            <?php else: ?>
                                                <span></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <button class="btn ghost sm mt-2" type="button"
                                data-repeat-add="spec-rows-<?= $groupIndex ?>">Add row</button>

                        <template id="spec-row-template-<?= $groupIndex ?>">
                            <div class="repeat-row" data-repeat-row style="background:#fff">
                                <?php foreach ($locales as $locale): ?>
                                    <div class="spec-pair" dir="<?= e(Lang::direction($locale)) ?>">
                                        <input class="input" type="text" placeholder="Label (<?= e(strtoupper($locale)) ?>)"
                                               name="specs[<?= $groupIndex ?>][rows][__INDEX__][<?= e($locale) ?>][label]" maxlength="190">
                                        <input class="input" type="text" placeholder="Value (<?= e(strtoupper($locale)) ?>)"
                                               name="specs[<?= $groupIndex ?>][rows][__INDEX__][<?= e($locale) ?>][value]" maxlength="500">
                                        <?php if ($locale === $locales[0]): ?>
                                            <button class="btn ghost sm" type="button" data-repeat-remove title="Remove row">×</button>
                                        <?php else: ?>
                                            <span></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </template>
                    </div>
                <?php endforeach; ?>
            </div>

            <template id="spec-group-template">
                <div class="repeat-row" data-repeat-row>
                    <div class="repeat-row__head">
                        <span>New group</span>
                        <span class="spacer"></span>
                        <button class="btn ghost sm" type="button" data-repeat-remove>Remove group</button>
                    </div>
                    <div class="grid-3">
                        <?php foreach ($locales as $locale): ?>
                            <div class="field" style="margin-bottom:0">
                                <label>Group title (<?= e(strtoupper($locale)) ?>)</label>
                                <input class="input" type="text" dir="<?= e(Lang::direction($locale)) ?>"
                                       name="specs[__INDEX__][title][<?= e($locale) ?>]" maxlength="190">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-3">
                        <?php foreach ($locales as $locale): ?>
                            <div class="spec-pair" dir="<?= e(Lang::direction($locale)) ?>">
                                <input class="input" type="text" placeholder="Label (<?= e(strtoupper($locale)) ?>)"
                                       name="specs[__INDEX__][rows][0][<?= e($locale) ?>][label]" maxlength="190">
                                <input class="input" type="text" placeholder="Value (<?= e(strtoupper($locale)) ?>)"
                                       name="specs[__INDEX__][rows][0][<?= e($locale) ?>][value]" maxlength="500">
                                <span></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p class="small muted mt-2">Save the product to add more rows to this group.</p>
                </div>
            </template>
        </div>
    </div>

    <!-- ============================================================ Features -->
    <div class="panel">
        <div class="panel__head">
            <h2>Capabilities</h2>
            <p>Bullet points shown beside the product summary.</p>
            <span class="spacer"></span>
            <button class="btn ghost sm" type="button" data-repeat-add="feature-rows">Add capability</button>
        </div>
        <div class="panel__body">
            <div id="feature-rows" data-repeat data-repeat-template="feature-template">
                <?php foreach ($features as $index => $feature): ?>
                    <div class="repeat-row" data-repeat-row>
                        <div class="repeat-row__head">
                            <span>Capability <?= $index + 1 ?></span>
                            <span class="spacer"></span>
                            <button class="btn ghost sm" type="button" data-repeat-remove>Remove</button>
                        </div>
                        <?php foreach ($locales as $locale): ?>
                            <input class="input" type="text" dir="<?= e(Lang::direction($locale)) ?>"
                                   placeholder="<?= e(Lang::nativeName($locale)) ?>"
                                   name="features[<?= $index ?>][<?= e($locale) ?>]" maxlength="500"
                                   value="<?= e($feature['texts'][$locale] ?? '') ?>">
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <template id="feature-template">
                <div class="repeat-row" data-repeat-row>
                    <div class="repeat-row__head">
                        <span>New capability</span>
                        <span class="spacer"></span>
                        <button class="btn ghost sm" type="button" data-repeat-remove>Remove</button>
                    </div>
                    <?php foreach ($locales as $locale): ?>
                        <input class="input" type="text" dir="<?= e(Lang::direction($locale)) ?>"
                               placeholder="<?= e(Lang::nativeName($locale)) ?>"
                               name="features[__INDEX__][<?= e($locale) ?>]" maxlength="500">
                    <?php endforeach; ?>
                </div>
            </template>
        </div>
    </div>

    <div class="panel">
        <div class="panel__foot">
            <button class="btn primary" type="submit"><?= $isEdit ? 'Save product' : 'Create product' ?></button>
            <a class="btn ghost" href="<?= e(Url::admin('products')) ?>">Cancel</a>
            <?php if ($isEdit): ?>
                <span class="spacer" style="margin-left:auto"></span>
                <a class="btn ghost" href="<?= e(Url::product((string) $product['slug'])) ?>" target="_blank" rel="noopener">
                    View on site ↗
                </a>
            <?php endif; ?>
        </div>
    </div>
</form>

<?php if ($isEdit): ?>
    <!-- ============================================================= Gallery -->
    <div class="panel">
        <div class="panel__head">
            <h2>Image gallery</h2>
            <p>The first image is used as the product cover. JPG, PNG, GIF or WebP, up to 8 MB.</p>
        </div>
        <div class="panel__body">
            <?php if ($product['images'] === []): ?>
                <p class="muted small mb-4">No images yet.</p>
            <?php else: ?>
                <div class="gallery-editor mb-4">
                    <?php foreach ($product['images'] as $image): ?>
                        <figure>
                            <img src="<?= e(Url::upload((string) $image['path'])) ?>"
                                 alt="<?= e(Media::alt($image, '')) ?>" loading="lazy">
                            <figcaption>
                                <form method="post"
                                      action="<?= e(Url::admin('products/' . $product['id'] . '/images/' . $image['id'] . '/delete')) ?>"
                                      data-confirm="Remove this image from the product?">
                                    <?= Csrf::field() ?>
                                    <button class="btn ghost sm" type="submit">Remove</button>
                                </form>
                            </figcaption>
                        </figure>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data"
                  action="<?= e(Url::admin('products/' . $product['id'] . '/images')) ?>">
                <?= Csrf::field() ?>
                <div class="field">
                    <label for="image">Add an image</label>
                    <input class="input" type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp" required>
                </div>
                <div class="grid-3">
                    <?php foreach ($locales as $locale): ?>
                        <div class="field">
                            <label for="alt_<?= e($locale) ?>">Alt text (<?= e(strtoupper($locale)) ?>)</label>
                            <input class="input" type="text" id="alt_<?= e($locale) ?>" name="alt_<?= e($locale) ?>"
                                   dir="<?= e(Lang::direction($locale)) ?>" maxlength="255">
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="btn primary" type="submit">Upload image</button>
            </form>
        </div>
    </div>

    <!-- ============================================================ Downloads -->
    <div class="panel">
        <div class="panel__head">
            <h2>Technical documents</h2>
            <p>Datasheets and catalogues offered for download on the product page. PDF, DOC, XLS or ZIP, up to 16 MB.</p>
        </div>
        <div class="panel__body">
            <?php if ($product['downloads'] !== []): ?>
                <div class="table-wrap mb-4">
                    <table class="table">
                        <tbody>
                            <?php foreach ($product['downloads'] as $download): ?>
                                <tr>
                                    <td>
                                        <a class="title" href="<?= e(Url::upload((string) $download['path'])) ?>" target="_blank" rel="noopener">
                                            <?= e($download['title_' . $default] ?: $download['original_name']) ?>
                                        </a>
                                        <div class="sub ltr">
                                            <?= e(strtoupper(pathinfo((string) $download['path'], PATHINFO_EXTENSION))) ?>
                                            · <?= e(Media::humanSize((int) $download['size'])) ?>
                                        </div>
                                    </td>
                                    <td class="actions">
                                        <form class="inline-form" method="post"
                                              action="<?= e(Url::admin('products/' . $product['id'] . '/downloads/' . $download['id'] . '/delete')) ?>"
                                              data-confirm="Remove this document from the product?">
                                            <?= Csrf::field() ?>
                                            <button class="btn ghost sm" type="submit">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data"
                  action="<?= e(Url::admin('products/' . $product['id'] . '/downloads')) ?>">
                <?= Csrf::field() ?>
                <div class="field">
                    <label for="document">Add a document</label>
                    <input class="input" type="file" id="document" name="document"
                           accept=".pdf,.doc,.docx,.xls,.xlsx,.zip" required>
                </div>
                <div class="grid-3">
                    <?php foreach ($locales as $locale): ?>
                        <div class="field">
                            <label for="title_<?= e($locale) ?>">Title (<?= e(strtoupper($locale)) ?>)</label>
                            <input class="input" type="text" id="title_<?= e($locale) ?>" name="title_<?= e($locale) ?>"
                                   dir="<?= e(Lang::direction($locale)) ?>" maxlength="190">
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="btn primary" type="submit">Upload document</button>
            </form>
        </div>
    </div>
<?php else: ?>
    <div class="alert info">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>
        </svg>
        <span>Images and technical documents can be uploaded once the product has been created.</span>
    </div>
<?php endif; ?>
