<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Synchroniser-token CSRF protection. One token per session, compared in
 * constant time, required on every state-changing request.
 */
final class Csrf
{
    public const FIELD = 'csrf_token';
    private const SESSION_KEY = '_csrf';

    public static function token(): string
    {
        if (!Session::has(self::SESSION_KEY)) {
            Session::put(self::SESSION_KEY, bin2hex(random_bytes(32)));
        }

        return (string) Session::get(self::SESSION_KEY);
    }

    public static function field(): string
    {
        return sprintf(
            '<input type="hidden" name="%s" value="%s">',
            self::FIELD,
            htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8')
        );
    }

    public static function check(Request $request): bool
    {
        $supplied = (string) ($request->raw(self::FIELD) ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        $expected = (string) Session::get(self::SESSION_KEY, '');

        return $expected !== '' && hash_equals($expected, $supplied);
    }

    /** Rotate after privilege changes (login, password change). */
    public static function rotate(): void
    {
        Session::put(self::SESSION_KEY, bin2hex(random_bytes(32)));
    }
}
