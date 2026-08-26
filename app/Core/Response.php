<?php
declare(strict_types=1);

namespace App\Core;

final class Response
{
    public static function html(string $body, int $status = 200, array $headers = []): never
    {
        self::send($body, $status, ['Content-Type' => 'text/html; charset=UTF-8'] + $headers);
    }

    public static function xml(string $body, int $status = 200): never
    {
        self::send($body, $status, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public static function text(string $body, int $status = 200): never
    {
        self::send($body, $status, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public static function json(array $data, int $status = 200): never
    {
        self::send(
            (string) json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            $status,
            ['Content-Type' => 'application/json; charset=UTF-8']
        );
    }

    public static function download(string $body, string $filename, string $mime = 'text/csv'): never
    {
        // Keep the filename free of anything that could break the header.
        $safe = preg_replace('/[^A-Za-z0-9._-]/', '_', $filename) ?: 'download';

        self::send($body, 200, [
            'Content-Type'        => $mime . '; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $safe . '"',
            'Cache-Control'       => 'no-store',
        ]);
    }

    public static function redirect(string $url, int $status = 302): never
    {
        // Only ever redirect within this application.
        if (!self::isSafeRedirect($url)) {
            $url = Url::base();
        }

        if (!headers_sent()) {
            header('Location: ' . $url, true, $status);
        }

        exit;
    }

    private static function isSafeRedirect(string $url): bool
    {
        // Relative path — always fine.
        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            return true;
        }

        $base = Url::base();
        if ($base !== '' && str_starts_with($url, $base)) {
            return true;
        }

        return false;
    }

    private static function send(string $body, int $status, array $headers): never
    {
        if (!headers_sent()) {
            http_response_code($status);

            foreach ($headers as $name => $value) {
                header($name . ': ' . $value);
            }
        }

        echo $body;
        exit;
    }
}
