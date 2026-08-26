<?php
/**
 * Page editor: the page's own text, plus its modular sections.
 *
 * @var array $page
 */

use App\Core\Csrf;
use App\Core\Lang;
use App\Core\Url;
use App\Models\Page;

$locales = Lang::enabled();
$default = Lang::default();

$tr = static fn (array $source, string $locale, string $key): string =>
    (string) ($source['translations'][$locale][$key] ?? '');

$typeLabels = [
    'hero'       => 'Hero banner',
    'richtext'   => 'Rich text',
    'image_text' => 'Image + text',
    'stats'      => 'Statistics',
    'features'   => 'Feature grid',
    'gallery'    => 'Gallery',
    'cta'        => 'Call to action',
    'quote'      => 'Quote',
];

// Guidance shown per section type, so the "Title | description" convention is
// discoverable without leaving the form.
$typeHints = [
    'features' => 'Body: one feature per line, written as "Title | description".',
    'stats'    => 'Body: one statistic per line, written as "Value | label".',
    'hero'     => 'Heading is the main title, subheading the eyebrow, body the lead paragraph.',
    'quote'    => 'Body is the quotation; heading is the attribution.',
];
?>
<div class="panel">
    <div class="panel__head">
        <h2><?= e($page['translations'][$default]['title'] ?? ucfirst((string) $page['slug'])) ?></h2>
        <span class="badge gray ltr">/<?= e($page['slug']) ?></span>
        <span class="spacer"></span>
        <a class="btn ghost sm" href="<?= e(Url::admin('pages')) ?>">← All pages</a>
    </div>
</div>

<!-- ================================================================ Page text -->
<form method="post" action="<?= e(Url::admin('pages/' . $page['id'])) ?>">
    <?= Csrf::field() ?>
    <div class="panel">
        <div class="panel__head">
            <h2>Page text</h2>
            <p>Title, lead paragraph and SEO metadata. Blank fields fall back to
               <strong><?= e(strtoupper($default)) ?></strong>.</p>
        </div>
        <div class="panel__body">
            <div class="lang-tabs" role="tablist" data-lang-tabs>
                <?php foreach ($locales as $index => $locale): ?>
                    <button type="button" class="lang-tab" role="tab" id="ptab-<?= e($locale) ?>"
                            aria-controls="ppanel-<?= e($locale) ?>"
                            aria-selected="<?= $index === 0 ? 'true' : 'false' ?>"
                            tabindex="<?= $index === 0 ? '0' : '-1' ?>"><?= e(Lang::nativeName($locale)) ?></button>
                <?php endforeach; ?>
            </div>

            <?php foreach ($locales as $index => $locale): ?>
                <div class="lang-panel" role="tabpanel" id="ppanel-<?= e($locale) ?>"
                     aria-labelledby="ptab-<?= e($locale) ?>" dir="<?= e(Lang::direction($locale)) ?>"
                     <?= $index === 0 ? '' : 'hidden' ?>>
                    <div class="field">
                        <label for="ptitle-<?= e($locale) ?>">Title</label>
                        <input class="input" type="text" id="ptitle-<?= e($locale) ?>"
                               name="tr[<?= e($locale) ?>][title]" maxlength="190"
                               value="<?= e($tr($page, $locale, 'title')) ?>">
                    </div>
                    <div class="field">
                        <label for="psub-<?= e($locale) ?>">Subtitle <span class="hint">— lead line under the page title</span></label>
                        <input class="input" type="text" id="psub-<?= e($locale) ?>"
                               name="tr[<?= e($locale) ?>][subtitle]" maxlength="320"
                               value="<?= e($tr($page, $locale, 'subtitle')) ?>">
                    </div>
                    <div class="field">
                        <label for="pbody-<?= e($locale) ?>">Body <span class="hint">— one paragraph per line</span></label>
                        <textarea class="textarea tall" id="pbody-<?= e($locale) ?>"
                                  name="tr[<?= e($locale) ?>][body]"><?= e($tr($page, $locale, 'body')) ?></textarea>
                    </div>
                    <div class="grid-2">
                        <div class="field">
                            <label for="pseot-<?= e($locale) ?>">SEO title</label>
                            <input class="input" type="text" id="pseot-<?= e($locale) ?>"
                                   name="tr[<?= e($locale) ?>][seo_title]" maxlength="190"
                                   value="<?= e($tr($page, $locale, 'seo_title')) ?>">
                        </div>
                        <div class="field">
                            <label for="pseod-<?= e($locale) ?>">SEO description</label>
                            <input class="input" type="text" id="pseod-<?= e($locale) ?>"
                                   name="tr[<?= e($locale) ?>][seo_description]" maxlength="320"
                                   value="<?= e($tr($page, $locale, 'seo_description')) ?>">
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="panel__foot">
            <button class="btn primary" type="submit">Save page text</button>
        </div>
    </div>
</form>

<!-- ================================================================= Sections -->
<div class="panel">
    <div class="panel__head">
        <h2>Sections</h2>
        <p>Content blocks rendered down the page, in sort order.</p>
    </div>
    <div class="panel__body">
        <?php if ($page['sections'] === []): ?>
            <p class="muted small">No sections yet — add one below.</p>
        <?php endif; ?>

        <?php foreach ($page['sections'] as $section): ?>
            <form method="post" enctype="multipart/form-data"
                  action="<?= e(Url::admin('pages/' . $page['id'] . '/sections')) ?>"
                  class="repeat-row" style="margin-bottom:16px">
                <?= Csrf::field() ?>
                <input type="hidden" name="section_id" value="<?= e((string) $section['id']) ?>">

                <div class="repeat-row__head">
                    <span><?= e($typeLabels[$section['type']] ?? $section['type']) ?></span>
                    <?php if (!$section['is_active']): ?><span class="badge gray">Hidden</span><?php endif; ?>
                    <span class="spacer"></span>
                    <span class="muted small">order <?= e((string) $section['sort_order']) ?></span>
                </div>

                <?php if (isset($typeHints[$section['type']])): ?>
                    <p class="small muted"><?= e($typeHints[$section['type']]) ?></p>
                <?php endif; ?>

                <div class="grid-3">
                    <div class="field" style="margin-bottom:0">
                        <label>Type</label>
                        <select class="select" name="type">
                            <?php foreach ($typeLabels as $value => $label): ?>
                                <option value="<?= e($value) ?>" <?= $section['type'] === $value ? 'selected' : '' ?>>
                                    <?= e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field" style="margin-bottom:0">
                        <label>Sort order</label>
                        <input class="input" type="number" name="sort_order" value="<?= e((string) $section['sort_order']) ?>">
                    </div>
                    <div class="field" style="margin-bottom:0">
                        <span class="label">Visibility</span>
                        <label class="checkbox">
                            <input type="checkbox" name="is_active" value="1" <?= $section['is_active'] ? 'checked' : '' ?>>
                            <span>Show this section</span>
                        </label>
                    </div>
                </div>

                <div class="lang-tabs mt-3" role="tablist" data-lang-tabs>
                    <?php foreach ($locales as $index => $locale): ?>
                        <button type="button" class="lang-tab" role="tab"
                                id="s<?= (int) $section['id'] ?>tab-<?= e($locale) ?>"
                                aria-controls="s<?= (int) $section['id'] ?>panel-<?= e($locale) ?>"
                                aria-selected="<?= $index === 0 ? 'true' : 'false' ?>"
                                tabindex="<?= $index === 0 ? '0' : '-1' ?>"><?= e(Lang::nativeName($locale)) ?></button>
                    <?php endforeach; ?>
                </div>

                <?php foreach ($locales as $index => $locale): ?>
                    <div class="lang-panel" role="tabpanel"
                         id="s<?= (int) $section['id'] ?>panel-<?= e($locale) ?>"
                         aria-labelledby="s<?= (int) $section['id'] ?>tab-<?= e($locale) ?>"
                         dir="<?= e(Lang::direction($locale)) ?>" <?= $index === 0 ? '' : 'hidden' ?>>
                        <div class="grid-2">
                            <div class="field">
                                <label>Heading</label>
                                <input class="input" type="text" name="tr[<?= e($locale) ?>][heading]" maxlength="255"
                                       value="<?= e($tr($section, $locale, 'heading')) ?>">
                            </div>
                            <div class="field">
                                <label>Subheading</label>
                                <input class="input" type="text" name="tr[<?= e($locale) ?>][subheading]" maxlength="500"
                                       value="<?= e($tr($section, $locale, 'subheading')) ?>">
                            </div>
                        </div>
                        <div class="field">
                            <label>Body</label>
                            <textarea class="textarea" name="tr[<?= e($locale) ?>][body]"><?= e($tr($section, $locale, 'body')) ?></textarea>
                        </div>
                        <div class="grid-2">
                            <div class="field">
                                <label>Button label</label>
                                <input class="input" type="text" name="tr[<?= e($locale) ?>][cta_label]" maxlength="120"
                                       value="<?= e($tr($section, $locale, 'cta_label')) ?>">
                            </div>
                            <div class="field">
                                <label>Button URL</label>
                                <input class="input" type="text" dir="ltr" name="tr[<?= e($locale) ?>][cta_url]" maxlength="255"
                                       value="<?= e($tr($section, $locale, 'cta_url')) ?>">
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="grid-2 mt-3">
                    <div class="field" style="margin-bottom:0">
                        <label>Section image</label>
                        <input class="input" type="file" name="media" accept="image/jpeg,image/png,image/gif,image/webp">
                        <?php if (!empty($section['image_path'])): ?>
                            <div class="mt-2" style="display:flex;gap:10px;align-items:center">
                                <img class="thumb" style="width:70px;height:70px"
                                     src="<?= e(Url::upload((string) $section['image_path'])) ?>" alt="">
                                <label class="checkbox">
                                    <input type="checkbox" name="remove_media" value="1">
                                    <span>Remove image</span>
                                </label>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="btn-row mt-3">
                    <button class="btn primary sm" type="submit">Save section</button>
                    <button class="btn danger sm" type="submit"
                            formaction="<?= e(Url::admin('pages/' . $page['id'] . '/sections/' . $section['id'] . '/delete')) ?>"
                            formnovalidate
                            onclick="return confirm('Delete this section permanently?')">Delete section</button>
                </div>
            </form>
        <?php endforeach; ?>
    </div>
</div>

<!-- ============================================================== New section -->
<div class="panel">
    <div class="panel__head"><h2>Add a section</h2></div>
    <form method="post" enctype="multipart/form-data" action="<?= e(Url::admin('pages/' . $page['id'] . '/sections')) ?>">
        <?= Csrf::field() ?>
        <div class="panel__body">
            <div class="grid-3">
                <div class="field">
                    <label for="new_type">Type</label>
                    <select class="select" id="new_type" name="type">
                        <?php foreach ($typeLabels as $value => $label): ?>
                            <option value="<?= e($value) ?>"><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="new_sort">Sort order</label>
                    <input class="input" type="number" id="new_sort" name="sort_order"
                           value="<?= e((string) count($page['sections'])) ?>">
                </div>
                <div class="field">
                    <span class="label">Visibility</span>
                    <label class="checkbox">
                        <input type="checkbox" name="is_active" value="1" checked>
                        <span>Show this section</span>
                    </label>
                </div>
            </div>

            <div class="lang-tabs" role="tablist" data-lang-tabs>
                <?php foreach ($locales as $index => $locale): ?>
                    <button type="button" class="lang-tab" role="tab" id="ntab-<?= e($locale) ?>"
                            aria-controls="npanel-<?= e($locale) ?>"
                            aria-selected="<?= $index === 0 ? 'true' : 'false' ?>"
                            tabindex="<?= $index === 0 ? '0' : '-1' ?>"><?= e(Lang::nativeName($locale)) ?></button>
                <?php endforeach; ?>
            </div>

            <?php foreach ($locales as $index => $locale): ?>
                <div class="lang-panel" role="tabpanel" id="npanel-<?= e($locale) ?>"
                     aria-labelledby="ntab-<?= e($locale) ?>" dir="<?= e(Lang::direction($locale)) ?>"
                     <?= $index === 0 ? '' : 'hidden' ?>>
                    <div class="grid-2">
                        <div class="field">
                            <label>Heading</label>
                            <input class="input" type="text" name="tr[<?= e($locale) ?>][heading]" maxlength="255">
                        </div>
                        <div class="field">
                            <label>Subheading</label>
                            <input class="input" type="text" name="tr[<?= e($locale) ?>][subheading]" maxlength="500">
                        </div>
                    </div>
                    <div class="field">
                        <label>Body</label>
                        <textarea class="textarea" name="tr[<?= e($locale) ?>][body]"></textarea>
                    </div>
                    <div class="grid-2">
                        <div class="field">
                            <label>Button label</label>
                            <input class="input" type="text" name="tr[<?= e($locale) ?>][cta_label]" maxlength="120">
                        </div>
                        <div class="field">
                            <label>Button URL</label>
                            <input class="input" type="text" dir="ltr" name="tr[<?= e($locale) ?>][cta_url]" maxlength="255">
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="field">
                <label for="new_media">Section image</label>
                <input class="input" type="file" id="new_media" name="media" accept="image/jpeg,image/png,image/gif,image/webp">
            </div>
        </div>
        <div class="panel__foot">
            <button class="btn primary" type="submit">Add section</button>
        </div>
    </form>
</div>
