<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Session bootstrap with hardened cookie flags, plus flash-message helpers.
 */
final class Session
{
    private const FLASH_KEY = '_flash';
    private const OLD_KEY   = '_old';

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $secure = Request::isSecure();

        session_name('rsid');
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        // Never accept a session id supplied in the URL.
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');

        session_start();

        // Age out the bags written by the previous request.
        $_SESSION[self::FLASH_KEY] = $_SESSION['_flash_next'] ?? [];
        $_SESSION[self::OLD_KEY]   = $_SESSION['_old_next'] ?? [];
        $_SESSION['_flash_next']   = [];
        $_SESSION['_old_next']     = [];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function put(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public static function destroy(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name() ?: 'rsid', '', [
                'expires'  => time() - 42000,
                'path'     => $params['path'],
                'domain'   => $params['domain'],
                'secure'   => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }

        session_destroy();
    }

    // --- Flash messages -----------------------------------------------------

    public static function flash(string $type, string $message): void
    {
        $_SESSION['_flash_next'][] = ['type' => $type, 'message' => $message];
    }

    /** @return array<int,array{type:string,message:string}> */
    public static function flashes(): array
    {
        $flashes                   = $_SESSION[self::FLASH_KEY] ?? [];
        $_SESSION[self::FLASH_KEY] = [];

        return $flashes;
    }

    /** Keep submitted form values for redisplay after a validation failure. */
    public static function flashInput(array $input): void
    {
        unset($input['password'], $input['password_confirmation'], $input['csrf_token']);
        $_SESSION['_old_next'] = $input;
    }

    public static function oldInput(): array
    {
        return $_SESSION[self::OLD_KEY] ?? [];
    }

    public static function old(string $key, mixed $default = ''): mixed
    {
        return $_SESSION[self::OLD_KEY][$key] ?? $default;
    }
}
