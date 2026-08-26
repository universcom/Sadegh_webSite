<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Plain-PHP template renderer with layout inheritance and section blocks.
 * Templates receive an escaping helper (`e`) and are expected to use it for
 * every dynamic value.
 */
final class View
{
    private static array $shared   = [];
    private static array $sections = [];
    private static array $stack    = [];

    public static function share(string $key, mixed $value): void
    {
        self::$shared[$key] = $value;
    }

    public static function shared(): array
    {
        return self::$shared;
    }

    public static function render(string $template, array $data = []): string
    {
        $path = self::path($template);
        $data = array_merge(self::$shared, $data);

        return self::capture($path, $data);
    }

    /** Render a template inside a layout. The template sets sections. */
    public static function renderWithLayout(string $template, string $layout, array $data = []): string
    {
        self::$sections = [];

        $content = self::render($template, $data);
        $data    = array_merge(self::$shared, $data, ['content' => $content]);

        return self::capture(self::path($layout), $data);
    }

    private static function path(string $template): string
    {
        // Templates are named with dots; reject anything that could escape the
        // views directory.
        if (!preg_match('/^[A-Za-z0-9_.\-]+$/', $template) || str_contains($template, '..')) {
            throw new RuntimeException('Invalid template name.');
        }

        $base = Config::get('app.base_path', dirname(__DIR__, 2)) . '/resources/views/';
        $path = $base . str_replace('.', '/', $template) . '.php';

        if (!is_file($path)) {
            throw new RuntimeException('View not found: ' . $template);
        }

        return $path;
    }

    private static function capture(string $path, array $data): string
    {
        $level = ob_get_level();
        extract($data, EXTR_SKIP);
        ob_start();

        try {
            /** @psalm-suppress UnresolvableInclude */
            include $path;
        } catch (\Throwable $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
            throw $e;
        }

        return (string) ob_get_clean();
    }

    // --- Sections -----------------------------------------------------------

    public static function start(string $name): void
    {
        self::$stack[] = $name;
        ob_start();
    }

    public static function stop(): void
    {
        $name = array_pop(self::$stack);

        if ($name === null) {
            throw new RuntimeException('View::stop() called without a matching start().');
        }

        self::$sections[$name] = (string) ob_get_clean();
    }

    public static function section(string $name, string $default = ''): string
    {
        return self::$sections[$name] ?? $default;
    }

    public static function hasSection(string $name): bool
    {
        return isset(self::$sections[$name]) && trim(self::$sections[$name]) !== '';
    }

    /** Include a partial template. */
    public static function partial(string $template, array $data = []): string
    {
        return self::render($template, $data);
    }
}
