<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Minimal .env reader. Values are cached in a static array rather than pushed
 * into $_ENV/getenv() so they cannot leak through phpinfo() or a stray var_dump.
 */
final class Env
{
    /** @var array<string,string> */
    private static array $values = [];
    private static bool $loaded = false;

    public static function load(string $path): void
    {
        self::$loaded = true;

        if (!is_readable($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);

            // Strip one layer of matching quotes.
            $len = strlen($value);
            if ($len >= 2) {
                $first = $value[0];
                if (($first === '"' || $first === "'") && $value[$len - 1] === $first) {
                    $value = substr($value, 1, -1);
                }
            }

            self::$values[$key] = $value;
        }
    }

    public static function loaded(): bool
    {
        return self::$loaded;
    }

    public static function has(string $key): bool
    {
        return array_key_exists($key, self::$values);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (!array_key_exists($key, self::$values)) {
            return $default;
        }

        $value = self::$values[$key];

        return match (strtolower($value)) {
            'true', '(true)'   => true,
            'false', '(false)' => false,
            'null', '(null)'   => null,
            'empty', '()'      => '',
            default            => $value,
        };
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = self::get($key, $default);

        return is_bool($value)
            ? $value
            : in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }

    public static function int(string $key, int $default = 0): int
    {
        $value = self::get($key, null);

        return is_numeric($value) ? (int) $value : $default;
    }
}
