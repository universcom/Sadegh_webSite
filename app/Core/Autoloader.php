<?php
declare(strict_types=1);

namespace App\Core;

/**
 * PSR-4 style autoloader for the two namespaces this project ships, so the
 * application runs on a host without Composer installed.
 */
final class Autoloader
{
    private const PREFIXES = [
        'App\\'                  => '/app/',
        'Database\\'             => '/database/',
        'PHPMailer\\PHPMailer\\' => '/app/Vendor/PHPMailer/',
    ];

    public static function register(string $basePath): void
    {
        spl_autoload_register(static function (string $class) use ($basePath): void {
            foreach (self::PREFIXES as $prefix => $directory) {
                if (!str_starts_with($class, $prefix)) {
                    continue;
                }

                $relative = substr($class, strlen($prefix));
                $path     = $basePath . $directory . str_replace('\\', '/', $relative) . '.php';

                if (is_file($path)) {
                    require_once $path;

                    return;
                }
            }
        });
    }
}
