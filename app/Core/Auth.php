<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Session-backed administrator authentication with database-recorded login
 * throttling. Roles are stored per user so authorisation can be widened later
 * without a schema change.
 */
final class Auth
{
    private const SESSION_KEY  = '_admin_id';
    private const FINGERPRINT  = '_admin_fp';
    private const LAST_SEEN    = '_admin_seen';
    private const IDLE_TIMEOUT = 7200;      // 2 hours
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT      = 900;       // 15 minutes

    private static ?array $user = null;

    public static function attempt(string $email, string $password, Request $request): bool
    {
        $db = Database::instance();
        $ip = $request->ip();

        if (self::isLockedOut($email, $ip)) {
            return false;
        }

        $user = $db->first(
            'SELECT * FROM admin_users WHERE email = :email LIMIT 1',
            ['email' => $email]
        );

        $hash = $user['password_hash'] ?? null;

        // Always run a hash comparison so a missing account and a wrong
        // password take the same amount of time.
        $valid = password_verify(
            $password,
            is_string($hash) ? $hash : '$2y$12$usesomesillystringforeverysingleusernotexist.abcdefghijk'
        );

        if (!$valid || $user === null || (int) $user['is_active'] !== 1) {
            self::recordAttempt($email, $ip, false);

            return false;
        }

        // Transparently upgrade hashes when the default cost changes.
        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            $db->update(
                'admin_users',
                ['password_hash' => password_hash($password, PASSWORD_DEFAULT)],
                'id = :id',
                ['id' => (int) $user['id']]
            );
        }

        self::recordAttempt($email, $ip, true);
        self::login($user, $request);

        return true;
    }

    public static function login(array $user, Request $request): void
    {
        // New session id on privilege change defeats session fixation.
        Session::regenerate();
        Csrf::rotate();

        Session::put(self::SESSION_KEY, (int) $user['id']);
        Session::put(self::FINGERPRINT, self::fingerprint($request));
        Session::put(self::LAST_SEEN, time());

        self::$user = $user;

        Database::instance()->update(
            'admin_users',
            ['last_login_at' => date('Y-m-d H:i:s'), 'last_login_ip' => $request->ip()],
            'id = :id',
            ['id' => (int) $user['id']]
        );
    }

    public static function logout(): void
    {
        self::$user = null;
        Session::destroy();
    }

    public static function check(?Request $request = null): bool
    {
        return self::user($request) !== null;
    }

    public static function user(?Request $request = null): ?array
    {
        if (self::$user !== null) {
            return self::$user;
        }

        $id = Session::get(self::SESSION_KEY);
        if (!is_int($id) && !ctype_digit((string) $id)) {
            return null;
        }

        // Idle timeout.
        $seen = (int) Session::get(self::LAST_SEEN, 0);
        if ($seen > 0 && (time() - $seen) > self::IDLE_TIMEOUT) {
            self::logout();

            return null;
        }
        Session::put(self::LAST_SEEN, time());

        // Binding the session to a coarse client fingerprint makes a stolen
        // cookie useless from a different browser.
        if ($request !== null) {
            $expected = (string) Session::get(self::FINGERPRINT, '');
            if ($expected !== '' && !hash_equals($expected, self::fingerprint($request))) {
                self::logout();

                return null;
            }
        }

        $user = Database::instance()->first(
            'SELECT * FROM admin_users WHERE id = :id AND is_active = 1 LIMIT 1',
            ['id' => (int) $id]
        );

        if ($user === null) {
            self::logout();

            return null;
        }

        return self::$user = $user;
    }

    public static function id(): ?int
    {
        $user = self::user();

        return $user === null ? null : (int) $user['id'];
    }

    public static function role(): string
    {
        return (string) (self::user()['role'] ?? 'guest');
    }

    public static function isAdmin(): bool
    {
        return in_array(self::role(), ['owner', 'admin'], true);
    }

    /** Only an owner may manage other administrator accounts. */
    public static function isOwner(): bool
    {
        return self::role() === 'owner';
    }

    private static function fingerprint(Request $request): string
    {
        return hash('sha256', $request->userAgent() . '|' . Config::get('app.key', ''));
    }

    // --- Throttling ---------------------------------------------------------

    public static function isLockedOut(string $email, string $ip): bool
    {
        return self::recentFailures($email, $ip) >= self::MAX_ATTEMPTS;
    }

    public static function secondsUntilRetry(string $email, string $ip): int
    {
        $last = Database::instance()->value(
            'SELECT MAX(attempted_at) FROM login_attempts
             WHERE success = 0 AND attempted_at > :since AND (email = :email OR ip_address = :ip)',
            [
                'since' => date('Y-m-d H:i:s', time() - self::LOCKOUT),
                'email' => $email,
                'ip'    => $ip,
            ]
        );

        if ($last === null) {
            return 0;
        }

        return max(0, self::LOCKOUT - (time() - strtotime((string) $last)));
    }

    private static function recentFailures(string $email, string $ip): int
    {
        return Database::instance()->count(
            'SELECT COUNT(*) FROM login_attempts
             WHERE success = 0 AND attempted_at > :since AND (email = :email OR ip_address = :ip)',
            [
                'since' => date('Y-m-d H:i:s', time() - self::LOCKOUT),
                'email' => $email,
                'ip'    => $ip,
            ]
        );
    }

    private static function recordAttempt(string $email, string $ip, bool $success): void
    {
        $db = Database::instance();

        $db->insert('login_attempts', [
            'email'        => mb_substr($email, 0, 190),
            'ip_address'   => $ip,
            'success'      => $success ? 1 : 0,
            'attempted_at' => date('Y-m-d H:i:s'),
        ]);

        if ($success) {
            // Clear the counter for this identity on a successful sign-in.
            $db->delete(
                'login_attempts',
                'success = 0 AND (email = :email OR ip_address = :ip)',
                ['email' => $email, 'ip' => $ip]
            );
        }

        // Opportunistic cleanup of rows older than a day.
        $db->delete('login_attempts', 'attempted_at < :cutoff', [
            'cutoff' => date('Y-m-d H:i:s', time() - 86400),
        ]);
    }
}
