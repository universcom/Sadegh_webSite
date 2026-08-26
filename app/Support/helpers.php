<?php
declare(strict_types=1);

use App\Core\Lang;
use App\Core\Url;

/**
 * Global helpers available inside every template. Kept deliberately small —
 * anything with real logic belongs in a class.
 */

if (!function_exists('e')) {
    /** Escape for HTML text and quoted attribute contexts. */
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('__')) {
    /** Translate a UI string. */
    function __(string $key, array $replace = []): string
    {
        return Lang::get($key, $replace);
    }
}

if (!function_exists('_e')) {
    /** Translate and escape. */
    function _e(string $key, array $replace = []): string
    {
        return e(Lang::get($key, $replace));
    }
}

if (!function_exists('num')) {
    /** Localise digits (Persian/Arabic numerals in RTL locales). */
    function num(int|float|string $value): string
    {
        return Lang::digits((string) $value);
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        return Url::to($path);
    }
}

if (!function_exists('lang_url')) {
    function lang_url(string $path = '', ?string $locale = null): string
    {
        return Url::lang($path, $locale);
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return Url::asset($path);
    }
}

if (!function_exists('upload_url')) {
    function upload_url(string $path): string
    {
        return Url::upload($path);
    }
}

if (!function_exists('excerpt')) {
    /** Trim text to a length without cutting mid-word. */
    function excerpt(?string $text, int $length = 160): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $text)) ?? '');

        if ($text === '' || mb_strlen($text) <= $length) {
            return $text;
        }

        $cut   = mb_substr($text, 0, $length);
        $space = mb_strrpos($cut, ' ');

        return rtrim($space !== false ? mb_substr($cut, 0, $space) : $cut, '،,.;:') . '…';
    }
}

if (!function_exists('slugify')) {
    /**
     * ASCII slug generator. Persian/Arabic titles have no Latin transliteration
     * here, so a non-Latin title falls back to a short hash suffix and the
     * administrator can refine the slug by hand.
     */
    function slugify(string $value, string $fallbackPrefix = 'item'): string
    {
        $value = trim($value);

        // Normalise Arabic-Indic digits to ASCII so numeric models survive.
        $value = str_replace(
            ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹','٠','١','٢','٣','٤','٥','٦','٧','٨','٩'],
            ['0','1','2','3','4','5','6','7','8','9','0','1','2','3','4','5','6','7','8','9'],
            $value
        );

        if (function_exists('iconv')) {
            $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
            if (is_string($ascii) && trim($ascii) !== '') {
                $value = $ascii;
            }
        }

        $slug = strtolower((string) preg_replace('/[^A-Za-z0-9]+/', '-', $value));
        $slug = trim($slug, '-');
        $slug = (string) preg_replace('/-{2,}/', '-', $slug);

        if ($slug === '' || !preg_match('/[a-z0-9]/', $slug)) {
            return $fallbackPrefix . '-' . substr(sha1($value . microtime()), 0, 8);
        }

        return mb_substr($slug, 0, 190);
    }
}

if (!function_exists('media_srcset')) {
    /**
     * Build a srcset from a media row's stored variant widths.
     *
     * @param array{path:string,basename?:string,variants?:string|array} $media
     */
    function media_srcset(array $media, string $subdirectory = 'media'): string
    {
        $variants = $media['variants'] ?? [];

        if (is_string($variants)) {
            $decoded  = json_decode($variants, true);
            $variants = is_array($decoded) ? $decoded : [];
        }

        $basename = $media['basename'] ?? null;

        if (!is_array($variants) || $variants === [] || !is_string($basename) || $basename === '') {
            return '';
        }

        $parts = [];
        foreach ($variants as $width) {
            $width   = (int) $width;
            $parts[] = Url::upload(sprintf('%s/%s-%d.jpg', $subdirectory, $basename, $width)) . ' ' . $width . 'w';
        }

        return implode(', ', $parts);
    }
}

if (!function_exists('date_local')) {
    /** Format a timestamp for display, with localised digits. */
    function date_local(?string $datetime, string $format = 'Y-m-d H:i'): string
    {
        if ($datetime === null || $datetime === '' || $datetime === '0000-00-00 00:00:00') {
            return '—';
        }

        $time = strtotime($datetime);

        return $time === false ? '—' : Lang::digits(date($format, $time));
    }
}
