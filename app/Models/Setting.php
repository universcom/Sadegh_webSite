<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Lang;

/**
 * Site settings. Loaded once per request into a static map so the templates can
 * read them freely without extra queries.
 */
final class Setting extends Model
{
    /** @var array<string,array<string,string>> skey => (lang => value) */
    private static array $cache  = [];
    private static bool $loaded  = false;

    /** Settings that carry a per-language value. */
    public const TRANSLATABLE = [
        'site_name', 'site_tagline', 'site_description',
        'address', 'working_hours', 'footer_about', 'seo_title', 'seo_description',
    ];

    public static function preload(): void
    {
        if (self::$loaded) {
            return;
        }

        self::$loaded = true;

        try {
            $rows = self::db()->all('SELECT skey, lang, svalue FROM settings');
        } catch (\Throwable) {
            // Before installation there is no table yet; templates use defaults.
            return;
        }

        foreach ($rows as $row) {
            self::$cache[$row['skey']][$row['lang']] = (string) ($row['svalue'] ?? '');
        }
    }

    public static function flush(): void
    {
        self::$cache  = [];
        self::$loaded = false;
    }

    /**
     * Read a setting. Translatable keys resolve
     * active language → default language → language-neutral → $default.
     */
    public static function get(string $key, string $default = '', ?string $locale = null): string
    {
        self::preload();

        $entry = self::$cache[$key] ?? [];
        $order = [$locale ?? Lang::current(), Lang::default(), ''];

        foreach ($order as $lang) {
            if (isset($entry[$lang]) && trim($entry[$lang]) !== '') {
                return $entry[$lang];
            }
        }

        return $default;
    }

    public static function has(string $key): bool
    {
        self::preload();

        return self::get($key) !== '';
    }

    /** @return array<string,array<string,string>> */
    public static function all(): array
    {
        self::preload();

        return self::$cache;
    }

    public static function put(string $key, string $value, string $lang = '', string $group = 'general'): void
    {
        $db = self::db();

        $id = $db->value(
            'SELECT id FROM settings WHERE skey = :k AND lang = :l LIMIT 1',
            ['k' => $key, 'l' => $lang]
        );

        if ($id !== null) {
            $db->update('settings', ['svalue' => $value], 'id = :id', ['id' => (int) $id]);
        } else {
            $db->insert('settings', [
                'skey'       => $key,
                'lang'       => $lang,
                'svalue'     => $value,
                'group_name' => $group,
            ]);
        }

        self::$cache[$key][$lang] = $value;
    }

    /** Social links, skipping any that are unset. @return array<string,string> */
    public static function socialLinks(): array
    {
        $links = [];

        foreach (['instagram', 'linkedin', 'telegram', 'whatsapp', 'youtube', 'x', 'aparat'] as $network) {
            $url = self::get('social_' . $network);
            if ($url !== '') {
                $links[$network] = $url;
            }
        }

        return $links;
    }

    /** Phone numbers as a list. */
    public static function phones(): array
    {
        return self::lines(self::get('phones'));
    }

    public static function emails(): array
    {
        return self::lines(self::get('emails'));
    }
}
