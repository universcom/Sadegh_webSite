<?php
declare(strict_types=1);

namespace App\Models;

final class AdminUser extends Model
{
    public const ROLES = ['owner', 'admin', 'editor'];

    public static function all(): array
    {
        return self::db()->all(
            'SELECT id, name, email, role, is_active, last_login_at, created_at
             FROM admin_users ORDER BY id ASC'
        );
    }

    public static function find(int $id): ?array
    {
        return self::db()->first('SELECT * FROM admin_users WHERE id = :id LIMIT 1', ['id' => $id]);
    }

    public static function findByEmail(string $email): ?array
    {
        return self::db()->first('SELECT * FROM admin_users WHERE email = :e LIMIT 1', ['e' => $email]);
    }

    public static function emailExists(string $email, ?int $ignoreId = null): bool
    {
        $sql    = 'SELECT id FROM admin_users WHERE email = :e';
        $params = ['e' => $email];

        if ($ignoreId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $ignoreId;
        }

        return self::db()->value($sql . ' LIMIT 1', $params) !== null;
    }

    public static function create(string $name, string $email, string $password, string $role = 'editor'): int
    {
        return self::db()->insert('admin_users', [
            'name'          => $name,
            'email'         => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role'          => in_array($role, self::ROLES, true) ? $role : 'editor',
            'is_active'     => 1,
        ]);
    }

    public static function update(int $id, array $data): void
    {
        if (isset($data['password']) && $data['password'] !== '') {
            $data['password_hash'] = password_hash((string) $data['password'], PASSWORD_DEFAULT);
        }
        unset($data['password'], $data['password_confirmation']);

        if ($data !== []) {
            self::db()->update('admin_users', $data, 'id = :id', ['id' => $id]);
        }
    }

    public static function delete(int $id): void
    {
        self::db()->delete('admin_users', 'id = :id', ['id' => $id]);
    }

    /** Guard against removing or disabling the last usable owner. */
    public static function activeOwnerCount(): int
    {
        return self::db()->count("SELECT COUNT(*) FROM admin_users WHERE role = 'owner' AND is_active = 1");
    }

    public static function count(): int
    {
        return self::db()->count('SELECT COUNT(*) FROM admin_users WHERE is_active = 1');
    }
}
