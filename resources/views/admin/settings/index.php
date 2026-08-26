<?php
/**
 * Site settings.
 *
 * @var array $neutral
 * @var array $translated
 */

use App\Core\Csrf;
use App\Core\Lang;
use App\Core\Url;
use App\Models\Setting;

$locales = Lang::enabled();

$labels = [
    'phones'           => ['Telephone numbers', 'One per line. Shown in the header, footer and contact page.'],
    'emails'           => ['E-mail addresses', 'One per line. The first is used as the fallback notification address.'],
    'postal_code'      => ['Postal code', ''],
    'city'             => ['City', 'Used in the Organization structured data.'],
    'map_url'          => ['Map link', 'A link to the location, e.g. a Google Maps share URL.'],
    'map_embed'        => ['Map embed URL', 'The src URL of a Google Maps embed. Leave blank to hide the map.'],
    'website'          => ['Public website', ''],
    'founded_year'     => ['Year founded', ''],
    'social_instagram' => ['Instagram', ''],
    'social_linkedin'  => ['LinkedIn', ''],
    'social_telegram'  => ['Telegram', ''],
    'social_whatsapp'  => ['WhatsApp', ''],
    'social_youtube'   => ['YouTube', ''],
    'social_aparat'    => ['Aparat', ''],
];

$trLabels = [
    'site_name'       => ['Company name', 'Shown in the header, footer and page titles.'],
    'site_tagline'    => ['Tagline', 'Short strapline under the logo.'],
    'seo_title'       => ['Default SEO title', 'Used on the home page and as a fallback.'],
    'seo_description' => ['Default SEO description', 'Used when a page has no description of its own.'],
    'address'         => ['Address', ''],
    'working_hours'   => ['Working hours', 'Leave blank to hide this from the contact page.'],
    'footer_about'    => ['Footer description', 'Short paragraph in the footer.'],
];

$multiline = ['phones', 'emails', 'address', 'working_hours', 'footer_about', 'seo_description'];
?>
<form method="post" action="<?= e(Url::admin('settings')) ?>" enctype="multipart/form-data">
    <?= Csrf::field() ?>

    <div class="panel">
        <div class="panel__head">
            <h2>Company details</h2>
            <p>These appear across the site in every language.</p>
        </div>
        <div class="panel__body">
            <div class="lang-tabs" role="tablist" data-lang-tabs>
                <?php foreach ($locales as $index => $locale): ?>
                    <button type="button" class="lang-tab" role="tab" id="stab-<?= e($locale) ?>"
                            aria-controls="spanel-<?= e($locale) ?>"
                            aria-selected="<?= $index === 0 ? 'true' : 'false' ?>"
                            tabindex="<?= $index === 0 ? '0' : '-1' ?>"><?= e(Lang::nativeName($locale)) ?></button>
                <?php endforeach; ?>
            </div>

            <?php foreach ($locales as $index => $locale): ?>
                <div class="lang-panel" role="tabpanel" id="spanel-<?= e($locale) ?>"
                     aria-labelledby="stab-<?= e($locale) ?>" dir="<?= e(Lang::direction($locale)) ?>"
                     <?= $index === 0 ? '' : 'hidden' ?>>
                    <?php foreach ($translated as $key): ?>
                        <?php [$label, $hint] = $trLabels[$key] ?? [$key, '']; ?>
                        <div class="field">
                            <label for="<?= e($key . '-' . $locale) ?>">
                                <?= e($label) ?>
                                <?php if ($hint !== ''): ?><span class="hint">— <?= e($hint) ?></span><?php endif; ?>
                            </label>
                            <?php if (in_array($key, $multiline, true)): ?>
                                <textarea class="textarea" id="<?= e($key . '-' . $locale) ?>" style="min-height:80px"
                                          name="tr[<?= e($locale) ?>][<?= e($key) ?>]"><?= e(Setting::get($key, '', $locale)) ?></textarea>
                            <?php else: ?>
                                <input class="input" type="text" id="<?= e($key . '-' . $locale) ?>"
                                       name="tr[<?= e($locale) ?>][<?= e($key) ?>]"
                                       value="<?= e(Setting::get($key, '', $locale)) ?>">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="panel">
        <div class="panel__head">
            <h2>Contact information</h2>
            <p>Shared across all languages.</p>
        </div>
        <div class="panel__body">
            <div class="grid-2">
                <?php foreach (['phones', 'emails'] as $key): ?>
                    <?php [$label, $hint] = $labels[$key]; ?>
                    <div class="field">
                        <label for="<?= e($key) ?>"><?= e($label) ?> <span class="hint">— <?= e($hint) ?></span></label>
                        <textarea class="textarea" id="<?= e($key) ?>" name="<?= e($key) ?>" dir="ltr"
                                  style="min-height:96px"><?= e(Setting::get($key)) ?></textarea>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="grid-3">
                <?php foreach (['postal_code', 'city', 'founded_year'] as $key): ?>
                    <?php [$label, $hint] = $labels[$key]; ?>
                    <div class="field">
                        <label for="<?= e($key) ?>"><?= e($label) ?></label>
                        <input class="input" type="text" id="<?= e($key) ?>" name="<?= e($key) ?>" dir="ltr"
                               value="<?= e(Setting::get($key)) ?>">
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="grid-2">
                <?php foreach (['website', 'map_url'] as $key): ?>
                    <?php [$label, $hint] = $labels[$key]; ?>
                    <div class="field">
                        <label for="<?= e($key) ?>">
                            <?= e($label) ?><?php if ($hint !== ''): ?> <span class="hint">— <?= e($hint) ?></span><?php endif; ?>
                        </label>
                        <input class="input" type="url" id="<?= e($key) ?>" name="<?= e($key) ?>" dir="ltr"
                               value="<?= e(Setting::get($key)) ?>">
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="field">
                <label for="map_embed"><?= e($labels['map_embed'][0]) ?> <span class="hint">— <?= e($labels['map_embed'][1]) ?></span></label>
                <input class="input" type="url" id="map_embed" name="map_embed" dir="ltr"
                       value="<?= e(Setting::get('map_embed')) ?>">
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel__head">
            <h2>Social media</h2>
            <p>Leave a field blank to hide that icon from the footer.</p>
        </div>
        <div class="panel__body">
            <div class="grid-3">
                <?php foreach (['social_instagram', 'social_linkedin', 'social_telegram', 'social_whatsapp', 'social_youtube', 'social_aparat'] as $key): ?>
                    <div class="field">
                        <label for="<?= e($key) ?>"><?= e($labels[$key][0]) ?></label>
                        <input class="input" type="url" id="<?= e($key) ?>" name="<?= e($key) ?>" dir="ltr"
                               placeholder="https://" value="<?= e(Setting::get($key)) ?>">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel__head">
            <h2>Branding</h2>
            <p>Uploading a new file replaces the current one. Leave blank to keep the built-in logo,
               which was taken from the company's own printed materials.</p>
        </div>
        <div class="panel__body">
            <div class="grid-2">
                <div class="field">
                    <label for="logo">Logo</label>
                    <input class="input" type="file" id="logo" name="logo" accept="image/jpeg,image/png,image/webp">
                    <img class="thumb mt-2" style="width:70px;height:70px"
                         src="<?= e(Setting::get('logo_path') !== '' ? Url::upload(Setting::get('logo_path')) : asset('img/logo-mark.png')) ?>" alt="Current logo">
                </div>
                <div class="field">
                    <label for="favicon">Favicon</label>
                    <input class="input" type="file" id="favicon" name="favicon" accept="image/png,image/jpeg">
                    <img class="thumb mt-2" style="width:40px;height:40px"
                         src="<?= e(Setting::get('favicon_path') !== '' ? Url::upload(Setting::get('favicon_path')) : asset('img/favicon-48.png')) ?>" alt="Current favicon">
                </div>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel__head">
            <h2>E-mail delivery</h2>
        </div>
        <div class="panel__body">
            <div class="alert info">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>
                </svg>
                <span>
                    SMTP credentials live in the <code>.env</code> file, not in the database, so they are never
                    served to a browser or stored in a backup of the site content. Edit
                    <code>MAIL_HOST</code>, <code>MAIL_PORT</code>, <code>MAIL_USERNAME</code>,
                    <code>MAIL_PASSWORD</code> and <code>MAIL_NOTIFY_TO</code> there.
                    Enquiries are always saved to the database even when e-mail delivery fails.
                </span>
            </div>
        </div>
        <div class="panel__foot">
            <button class="btn primary" type="submit">Save all settings</button>
        </div>
    </div>
</form>
