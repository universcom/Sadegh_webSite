<?php
declare(strict_types=1);

namespace App\Core;

use Throwable;

/**
 * Append-only file logger. Anything that could carry a secret (DSNs, SMTP
 * passwords) is redacted before it is written.
 */
final class Logger
{
    private const REDACTIONS = [
        '/(password|passwd|pwd|secret|api[_-]?key|token)\s*[=:]\s*\S+/i' => '$1=[redacted]',
        '/\bmysql:[^\s\'"]+/i'                                          => 'mysql:[redacted]',
    ];

    public static function error(string $message, ?Throwable $e = null, array $context = []): void
    {
        self::write('ERROR', $message, $e, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::write('WARNING', $message, null, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::write('INFO', $message, null, $context);
    }

    private static function write(string $level, string $message, ?Throwable $e, array $context): void
    {
        $directory = Config::get('app.storage_path', dirname(__DIR__, 2) . '/storage') . '/logs';

        if (!is_dir($directory) && !@mkdir($directory, 0o755, true) && !is_dir($directory)) {
            return;
        }

        $line = sprintf('[%s] %s: %s', date('Y-m-d H:i:s'), $level, $message);

        if ($context !== []) {
            $line .= ' ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        if ($e !== null) {
            $line .= sprintf(
                ' | %s: %s @ %s:%d',
                $e::class,
                $e->getMessage(),
                // Only the file name — never the absolute server path.
                basename($e->getFile()),
                $e->getLine()
            );
        }

        foreach (self::REDACTIONS as $pattern => $replacement) {
            $line = (string) preg_replace($pattern, $replacement, $line);
        }

        @file_put_contents(
            $directory . '/app-' . date('Y-m') . '.log',
            $line . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }
}
