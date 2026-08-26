<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Locale registry + UI string catalogue.
 *
 * Content translation lives in the database (one *_translations row per
 * language); this class only handles interface chrome and locale metadata.
 * Adding a language means: add it here, add resources/lang/<code>.php, and add
 * the code to APP_LOCALES.
 */
final class Lang
{
    /** @var array<string,array{name:string,native:string,dir:string,html:string}> */
    public const SUPPORTED = [
        'fa' => ['name' => 'Persian', 'native' => 'فارسی',    'dir' => 'rtl', 'html' => 'fa-IR'],
        'en' => ['name' => 'English', 'native' => 'English',  'dir' => 'ltr', 'html' => 'en'],
        'ar' => ['name' => 'Arabic',  'native' => 'العربية',  'dir' => 'rtl', 'html' => 'ar'],
    ];

    private static string $current = 'fa';
    private static array $lines    = [];
    private static array $fallback = [];

    /** @return array<int,string> */
    public static function enabled(): array
    {
        $configured = Config::get('app.locales', ['fa', 'en', 'ar']);
        $enabled    = array_values(array_intersect($configured, array_keys(self::SUPPORTED)));

        return $enabled === [] ? ['fa'] : $enabled;
    }

    public static function default(): string
    {
        $default = (string) Config::get('app.default_locale', 'fa');

        return in_array($default, self::enabled(), true) ? $default : self::enabled()[0];
    }

    public static function isSupported(string $code): bool
    {
        return in_array($code, self::enabled(), true);
    }

    public static function set(string $code): void
    {
        self::$current = self::isSupported($code) ? $code : self::default();
        self::$lines   = self::loadFile(self::$current);

        // The default locale is the fallback for any key a translation misses.
        self::$fallback = self::$current === self::default()
            ? self::$lines
            : self::loadFile(self::default());

        // Affects date/number formatting helpers only; output stays UTF-8.
        setlocale(LC_TIME, self::$current);
    }

    private static function loadFile(string $code): array
    {
        $path = Config::get('app.base_path', dirname(__DIR__, 2)) . '/resources/lang/' . $code . '.php';

        if (!is_file($path)) {
            return [];
        }

        $lines = require $path;

        return is_array($lines) ? $lines : [];
    }

    public static function current(): string
    {
        return self::$current;
    }

    public static function direction(?string $code = null): string
    {
        return self::SUPPORTED[$code ?? self::$current]['dir'] ?? 'ltr';
    }

    public static function isRtl(?string $code = null): bool
    {
        return self::direction($code) === 'rtl';
    }

    public static function htmlLang(?string $code = null): string
    {
        return self::SUPPORTED[$code ?? self::$current]['html'] ?? 'en';
    }

    public static function nativeName(string $code): string
    {
        return self::SUPPORTED[$code]['native'] ?? $code;
    }

    /** Translate a UI key, with :placeholder interpolation. */
    public static function get(string $key, array $replace = []): string
    {
        $line = self::$lines[$key] ?? self::$fallback[$key] ?? $key;

        foreach ($replace as $search => $value) {
            $line = str_replace(':' . $search, (string) $value, $line);
        }

        return $line;
    }

    /**
     * Convert Western digits to Persian/Arabic-Indic digits so numbers read
     * naturally inside RTL copy. Latin-script locales are returned untouched.
     */
    public static function digits(string $value, ?string $code = null): string
    {
        $code = $code ?? self::$current;

        $map = match ($code) {
            'fa'    => ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'],
            'ar'    => ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'],
            default => null,
        };

        if ($map === null) {
            return $value;
        }

        return str_replace(['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'], $map, $value);
    }
}
