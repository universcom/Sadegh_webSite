<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Immutable-ish view over the current HTTP request.
 */
final class Request
{
    private string $method;
    private string $path;
    private array $query;
    private array $body;
    private array $files;

    public function __construct()
    {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        // Method override, so HTML forms can express PUT/PATCH/DELETE.
        if ($this->method === 'POST' && isset($_POST['_method'])) {
            $override = strtoupper((string) $_POST['_method']);
            if (in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
                $this->method = $override;
            }
        }

        $this->path  = self::resolvePath();
        $this->query = $_GET;
        $this->body  = $_POST;
        $this->files = $_FILES;
    }

    /**
     * Work out the application-relative path, tolerating both mod_rewrite
     * (/fa/products) and the PATH_INFO fallback (/index.php/fa/products), and
     * installations in a sub-directory.
     */
    private static function resolvePath(): string
    {
        $uri  = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $path = parse_url($uri, PHP_URL_PATH);
        $path = is_string($path) ? $path : '/';
        $path = rawurldecode($path);

        // Strip the directory the front controller lives in.
        $base = str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '')));
        if ($base !== '' && $base !== '/' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }

        // Strip "/index.php" when running without rewrites.
        if (preg_match('#^/?index\.php#i', $path)) {
            $path = (string) preg_replace('#^/?index\.php#i', '', $path);
        }

        $path = '/' . trim($path, '/');

        // Collapse duplicate slashes and reject traversal segments outright.
        $path = (string) preg_replace('#/+#', '/', $path);

        return str_contains($path, '..') ? '/' : $path;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function path(): string
    {
        return $this->path;
    }

    /** Path segments with empty entries removed. @return array<int,string> */
    public function segments(): array
    {
        return array_values(array_filter(explode('/', $this->path), static fn ($s) => $s !== ''));
    }

    public function query(string $key, mixed $default = null): mixed
    {
        $value = $this->query[$key] ?? $default;

        return is_string($value) ? trim($value) : $value;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        $value = $this->body[$key] ?? $default;

        return is_string($value) ? trim($value) : $value;
    }

    public function raw(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->body);
    }

    public function boolean(string $key): bool
    {
        return in_array((string) ($this->body[$key] ?? ''), ['1', 'on', 'true', 'yes'], true);
    }

    public function integer(string $key, int $default = 0): int
    {
        $value = $this->body[$key] ?? $this->query[$key] ?? null;

        return is_numeric($value) ? (int) $value : $default;
    }

    public function all(): array
    {
        return $this->body;
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function files(): array
    {
        return $this->files;
    }

    public function ip(): string
    {
        // Only REMOTE_ADDR is trustworthy without a vetted proxy allow-list.
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');

        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
    }

    public function userAgent(): string
    {
        return mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
    }

    public function referer(): string
    {
        return (string) ($_SERVER['HTTP_REFERER'] ?? '');
    }

    public function isAjax(): bool
    {
        return strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
    }

    public static function isSecure(): bool
    {
        if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
            return true;
        }

        if (((int) ($_SERVER['SERVER_PORT'] ?? 0)) === 443) {
            return true;
        }

        return strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
    }

    /** Scheme + host, derived from the request (used only as an APP_URL fallback). */
    public static function origin(): string
    {
        $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
        // Guard against Host-header injection into generated links.
        if (!preg_match('/^[A-Za-z0-9\.\-]+(:\d+)?$/', $host)) {
            $host = 'localhost';
        }

        return (self::isSecure() ? 'https://' : 'http://') . $host;
    }
}
