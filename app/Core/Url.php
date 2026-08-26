<?php
declare(strict_types=1);

namespace App\Core;

/**
 * URL construction. Public URLs are always language-prefixed
 * (/fa/products/slug); admin URLs never are.
 */
final class Url
{
    private static ?string $base = null;

    /** Application base URL with no trailing slash. */
    public static function base(): string
    {
        if (self::$base !== null) {
            return self::$base;
        }

        $configured = trim((string) Config::get('app.url', ''), '/');

        if ($configured !== '' && preg_match('#^https?://#i', $configured)) {
            return self::$base = $configured;
        }

        // Fall back to the current request, including any sub-directory.
        $dir = str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '')));
        $dir = ($dir === '/' || $dir === '.') ? '' : rtrim($dir, '/');

        return self::$base = Request::origin() . $dir;
    }

    public static function reset(): void
    {
        self::$base = null;
    }

    /** An absolute URL for a path that already includes any language prefix. */
    public static function to(string $path = ''): string
    {
        $path = trim($path, '/');

        return $path === '' ? self::base() . '/' : self::base() . '/' . $path;
    }

    /** A language-prefixed public URL: to('products', 'en') => /en/products */
    public static function lang(string $path = '', ?string $locale = null): string
    {
        $locale = $locale ?? Lang::current();
        $path   = trim($path, '/');

        return self::to($path === '' ? $locale : $locale . '/' . $path);
    }

    public static function home(?string $locale = null): string
    {
        return self::lang('', $locale);
    }

    public static function products(?string $locale = null): string
    {
        return self::lang(self::segment('products', $locale), $locale);
    }

    public static function category(string $slug, ?string $locale = null): string
    {
        return self::lang(self::segment('products', $locale) . '/' . self::segment('category', $locale) . '/' . $slug, $locale);
    }

    public static function product(string $slug, ?string $locale = null): string
    {
        return self::lang(self::segment('products', $locale) . '/' . $slug, $locale);
    }

    public static function research(?string $locale = null): string
    {
        return self::lang(self::segment('research', $locale), $locale);
    }

    public static function researchProject(string $slug, ?string $locale = null): string
    {
        return self::lang(self::segment('research', $locale) . '/' . $slug, $locale);
    }

    public static function about(?string $locale = null): string
    {
        return self::lang(self::segment('about', $locale), $locale);
    }

    public static function contact(?string $locale = null): string
    {
        return self::lang(self::segment('contact', $locale), $locale);
    }

    /**
     * URL segments stay ASCII in every language. Persian/Arabic slugs in the
     * path would be percent-encoded into unreadable URLs and are fragile in
     * older mail clients and chat apps, so the routing vocabulary is English
     * while all visible text is translated.
     */
    public static function segment(string $key, ?string $locale = null): string
    {
        return match ($key) {
            'products' => 'products',
            'category' => 'category',
            'research' => 'research',
            'about'    => 'about',
            'contact'  => 'contact',
            default    => $key,
        };
    }

    public static function admin(string $path = ''): string
    {
        $path = trim($path, '/');

        return self::to($path === '' ? 'admin' : 'admin/' . $path);
    }

    public static function asset(string $path): string
    {
        return self::to('assets/' . ltrim($path, '/'));
    }

    /** Public URL for a stored media file. */
    public static function upload(string $path): string
    {
        return self::to('uploads/' . ltrim($path, '/'));
    }

    /** Rebuild the current URL under a different language. */
    public static function switchLocale(string $locale, Request $request): string
    {
        $segments = $request->segments();

        if ($segments !== [] && Lang::isSupported($segments[0])) {
            array_shift($segments);
        }

        $query = $_GET;
        unset($query['_']);
        $suffix = $query === [] ? '' : '?' . http_build_query($query);

        return self::lang(implode('/', $segments), $locale) . $suffix;
    }

    /** Append/replace query parameters on the current URL. */
    public static function withQuery(array $params, Request $request): string
    {
        $query = array_merge($_GET, $params);
        $query = array_filter($query, static fn ($v) => $v !== null && $v !== '');

        $path = self::to(ltrim($request->path(), '/'));

        return $query === [] ? $path : $path . '?' . http_build_query($query);
    }
}
