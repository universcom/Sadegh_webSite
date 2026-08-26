<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Lang;
use App\Core\Request;
use App\Core\Uploader;
use App\Core\Validator;
use App\Models\Media;
use App\Models\Setting;

final class SettingController extends AdminController
{
    /** Language-neutral settings: key => [group, max length]. */
    private const NEUTRAL = [
        'phones'           => ['contact', 500],
        'emails'           => ['contact', 500],
        'postal_code'      => ['contact', 40],
        'city'             => ['contact', 90],
        'map_url'          => ['contact', 500],
        'map_embed'        => ['contact', 1000],
        'website'          => ['general', 255],
        'founded_year'     => ['general', 10],
        'social_instagram' => ['social', 255],
        'social_linkedin'  => ['social', 255],
        'social_telegram'  => ['social', 255],
        'social_whatsapp'  => ['social', 255],
        'social_youtube'   => ['social', 255],
        'social_aparat'    => ['social', 255],
    ];

    /** Per-language settings. */
    private const TRANSLATED = [
        'site_name'       => 190,
        'site_tagline'    => 255,
        'seo_title'       => 190,
        'seo_description' => 320,
        'address'         => 500,
        'working_hours'   => 255,
        'footer_about'    => 500,
    ];

    public function index(Request $request): never
    {
        $this->view('admin.settings.index', [
            'pageTitle'  => 'Site settings',
            'activeNav'  => 'settings',
            'neutral'    => array_keys(self::NEUTRAL),
            'translated' => array_keys(self::TRANSLATED),
            'errors'     => [],
        ]);
    }

    public function update(Request $request): never
    {
        $url = $this->adminUrl('settings');

        $this->requireCsrf($request, $url);
        $this->requireAdmin($url);

        $errors = [];

        // --- Language-neutral values ----------------------------------------
        foreach (self::NEUTRAL as $key => [$group, $maxLength]) {
            if (!$request->has($key)) {
                continue;
            }

            $value = trim((string) $request->input($key, ''));

            // URL-shaped settings are validated so a typo cannot produce a
            // broken link or an unexpected scheme in the footer.
            if ($value !== '' && (str_starts_with($key, 'social_') || in_array($key, ['website', 'map_url'], true))) {
                if (!filter_var($value, FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $value)) {
                    $errors[$key] = 'Enter a full address starting with http:// or https://';
                    continue;
                }
            }

            if ($key === 'map_embed' && $value !== '') {
                // Only accept a plain embed URL, never raw <iframe> markup.
                if (!filter_var($value, FILTER_VALIDATE_URL) || !preg_match('#^https://#i', $value)) {
                    $errors[$key] = 'The map embed must be an https:// URL (the src of the embed, not the iframe tag).';
                    continue;
                }
            }

            Setting::put($key, mb_substr($value, 0, $maxLength), '', $group);
        }

        // --- Per-language values ---------------------------------------------
        $raw = $request->raw('tr', []);

        if (is_array($raw)) {
            foreach (Lang::enabled() as $locale) {
                $values = is_array($raw[$locale] ?? null) ? $raw[$locale] : [];

                foreach (self::TRANSLATED as $key => $maxLength) {
                    if (!array_key_exists($key, $values)) {
                        continue;
                    }

                    Setting::put($key, mb_substr(trim((string) $values[$key]), 0, $maxLength), $locale, 'general');
                }
            }
        }

        // --- Logo / favicon ---------------------------------------------------
        foreach (['logo', 'favicon'] as $key) {
            $file = $request->file($key);

            if (!Uploader::wasUploaded($file)) {
                continue;
            }

            try {
                $mediaId = Media::storeImage($file);
                $media   = Media::find($mediaId);

                if ($media !== null) {
                    Setting::put($key . '_path', (string) $media['path'], '', 'branding');
                }
            } catch (\Throwable $e) {
                $errors[$key] = $e->getMessage();
            }
        }

        Setting::flush();

        if ($errors !== []) {
            $this->back($url, 'error', 'Saved, but some fields were rejected: ' . implode(' ', $errors));
        }

        $this->back($url, 'success', 'Settings saved.');
    }
}
