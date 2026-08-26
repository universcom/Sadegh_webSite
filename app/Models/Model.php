<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Lang;

/**
 * Shared behaviour for the translation-backed entities.
 *
 * Every read joins the translation row for the active language and falls back
 * to the default language when a field has not been translated yet — so a
 * half-translated site still renders complete pages.
 */
abstract class Model
{
    protected static function db(): Database
    {
        return Database::instance();
    }

    /**
     * SQL fragment that picks a translated column with a default-language
     * fallback. Both translation aliases must already be joined.
     */
    protected static function tr(string $column, string $primary = 't', string $fallback = 'tf'): string
    {
        return sprintf(
            'COALESCE(NULLIF(%1$s.`%3$s`, \'\'), NULLIF(%2$s.`%3$s`, \'\'))',
            $primary,
            $fallback,
            $column
        );
    }

    /** Language pair used by every translated query. */
    protected static function langParams(?string $locale = null): array
    {
        return [
            'lang'     => $locale ?? Lang::current(),
            'fallback' => Lang::default(),
        ];
    }

    /**
     * Replace the full set of translations for an entity.
     *
     * @param array<string,array<string,mixed>> $translations lang => fields
     */
    protected static function saveTranslations(string $table, string $foreignKey, int $id, array $translations): void
    {
        $db = self::db();

        foreach ($translations as $lang => $fields) {
            if (!Lang::isSupported($lang)) {
                continue;
            }

            $existing = $db->value(
                sprintf('SELECT id FROM `%s` WHERE `%s` = :id AND lang = :lang LIMIT 1', $table, $foreignKey),
                ['id' => $id, 'lang' => $lang]
            );

            if ($existing !== null) {
                $db->update($table, $fields, 'id = :id', ['id' => (int) $existing]);
            } else {
                $db->insert($table, $fields + [$foreignKey => $id, 'lang' => $lang]);
            }
        }
    }

    /** Split a newline-delimited text field into a clean list. */
    public static function lines(?string $text): array
    {
        if ($text === null || trim($text) === '') {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $lines = array_map(static fn (string $l): string => trim(ltrim($l, "-•*\t ")), $lines);

        return array_values(array_filter($lines, static fn (string $l): bool => $l !== ''));
    }

    /** Guarantee a slug is unique within a table, appending -2, -3, … */
    public static function uniqueSlug(string $table, string $slug, ?int $ignoreId = null): string
    {
        $db       = self::db();
        $base     = $slug;
        $attempt  = 1;

        while (true) {
            $sql    = sprintf('SELECT id FROM `%s` WHERE slug = :slug', $table);
            $params = ['slug' => $slug];

            if ($ignoreId !== null) {
                $sql .= ' AND id <> :ignore';
                $params['ignore'] = $ignoreId;
            }

            if ($db->value($sql . ' LIMIT 1', $params) === null) {
                return $slug;
            }

            $slug = $base . '-' . (++$attempt);
        }
    }
}
