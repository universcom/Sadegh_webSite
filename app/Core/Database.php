<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

/**
 * Thin PDO wrapper. Every query in the application goes through here, and every
 * one of these methods binds parameters — there is no string interpolation of
 * user input into SQL anywhere in the codebase.
 */
final class Database
{
    private static ?self $instance = null;
    private PDO $pdo;

    private function __construct(array $config)
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'],
            (int) $config['port'],
            $config['name'],
            $config['charset'] ?? 'utf8mb4'
        );

        try {
            $this->pdo = new PDO($dsn, $config['user'], $config['password'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // Real prepared statements, not driver-side emulation.
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_STRINGIFY_FETCHES  => false,
            ]);
        } catch (PDOException $e) {
            // The message can contain the DSN (host, db, user) — never surface it.
            Logger::error('Database connection failed', $e);
            throw new RuntimeException('Database connection failed.', 0, $e);
        }
    }

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self(Config::get('database'));
        }

        return self::$instance;
    }

    /** Build a connection outside the singleton (used by the installer). */
    public static function connect(array $config): self
    {
        return new self($config);
    }

    public static function reset(): void
    {
        self::$instance = null;
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    public function run(string $sql, array $params = []): PDOStatement
    {
        // Native prepared statements bind one value per placeholder occurrence,
        // so a named parameter used more than once (as the translation-fallback
        // joins do with :lang) must be expanded to positional placeholders.
        if ($params !== [] && !array_is_list($params)) {
            [$sql, $params] = self::expandNamedParameters($sql, $params);
        }

        $statement = $this->pdo->prepare($sql);

        foreach ($params as $key => $value) {
            $name = is_int($key) ? $key + 1 : $key;
            $type = match (true) {
                is_int($value)  => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                is_null($value) => PDO::PARAM_NULL,
                default         => PDO::PARAM_STR,
            };
            $statement->bindValue($name, $value, $type);
        }

        $statement->execute();

        return $statement;
    }

    /**
     * Rewrite :named placeholders to ? and build the matching positional list,
     * repeating a value for each occurrence of its name.
     *
     * Quoted regions and `identifiers` are skipped so a colon inside a string
     * literal is never mistaken for a placeholder.
     *
     * @return array{0:string,1:array<int,mixed>}
     */
    private static function expandNamedParameters(string $sql, array $params): array
    {
        $out      = '';
        $ordered  = [];
        $length   = strlen($sql);
        $index    = 0;

        while ($index < $length) {
            $char = $sql[$index];

            // Copy quoted strings and quoted identifiers verbatim.
            if ($char === "'" || $char === '"' || $char === '`') {
                $quote = $char;
                $out  .= $char;
                $index++;

                while ($index < $length) {
                    $current = $sql[$index];
                    $out    .= $current;
                    $index++;

                    if ($current === '\\' && $quote !== '`' && $index < $length) {
                        $out .= $sql[$index];
                        $index++;
                        continue;
                    }

                    if ($current === $quote) {
                        // A doubled quote is an escaped quote, not a terminator.
                        if ($index < $length && $sql[$index] === $quote) {
                            $out .= $sql[$index];
                            $index++;
                            continue;
                        }
                        break;
                    }
                }

                continue;
            }

            if ($char === ':' && preg_match('/^:([a-zA-Z_][a-zA-Z0-9_]*)/', substr($sql, $index), $m)) {
                $name = $m[1];

                if (array_key_exists($name, $params)) {
                    $out      .= '?';
                    $ordered[] = $params[$name];
                    $index    += strlen($m[0]);

                    continue;
                }
            }

            $out .= $char;
            $index++;
        }

        return [$out, $ordered];
    }

    /** @return array<int,array<string,mixed>> */
    public function all(string $sql, array $params = []): array
    {
        return $this->run($sql, $params)->fetchAll();
    }

    /** @return array<string,mixed>|null */
    public function first(string $sql, array $params = []): ?array
    {
        $row = $this->run($sql, $params)->fetch();

        return $row === false ? null : $row;
    }

    public function value(string $sql, array $params = []): mixed
    {
        $value = $this->run($sql, $params)->fetchColumn();

        return $value === false ? null : $value;
    }

    public function count(string $sql, array $params = []): int
    {
        return (int) $this->value($sql, $params);
    }

    public function insert(string $table, array $data): int
    {
        $columns      = array_keys($data);
        $placeholders = array_map(static fn (string $c): string => ':' . $c, $columns);

        $sql = sprintf(
            'INSERT INTO `%s` (%s) VALUES (%s)',
            $table,
            '`' . implode('`, `', $columns) . '`',
            implode(', ', $placeholders)
        );

        $this->run($sql, $data);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $assignments = [];
        $params      = [];

        foreach ($data as $column => $value) {
            $assignments[]        = sprintf('`%s` = :set_%s', $column, $column);
            $params['set_' . $column] = $value;
        }

        $sql = sprintf(
            'UPDATE `%s` SET %s WHERE %s',
            $table,
            implode(', ', $assignments),
            $where
        );

        return $this->run($sql, $params + $whereParams)->rowCount();
    }

    public function delete(string $table, string $where, array $params = []): int
    {
        return $this->run(sprintf('DELETE FROM `%s` WHERE %s', $table, $where), $params)->rowCount();
    }

    public function transaction(callable $callback): mixed
    {
        $this->pdo->beginTransaction();

        try {
            $result = $callback($this);
            $this->pdo->commit();

            return $result;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Expand a list into positional placeholders for an IN () clause.
     *
     * @return array{0:string,1:array<int,mixed>}
     */
    public static function inClause(array $values): array
    {
        if ($values === []) {
            return ['NULL', []];
        }

        return [implode(', ', array_fill(0, count($values), '?')), array_values($values)];
    }
}
