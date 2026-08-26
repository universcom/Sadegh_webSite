<?php
declare(strict_types=1);

namespace App\Models;

final class Category extends Model
{
    private const SELECT = 'c.id, c.slug, c.sort_order, c.is_active, c.icon, c.parent_id,
            m.path AS image_path, m.basename AS image_basename, m.variants AS image_variants,
            m.width AS image_width, m.height AS image_height';

    private const JOINS = '
        FROM categories c
        LEFT JOIN category_translations t  ON t.category_id  = c.id AND t.lang  = :lang
        LEFT JOIN category_translations tf ON tf.category_id = c.id AND tf.lang = :fallback
        LEFT JOIN media m                  ON m.id           = c.image_id';

    /** Active categories with a live product count, ordered for the menu. */
    public static function published(?string $locale = null): array
    {
        return self::db()->all(
            'SELECT ' . self::SELECT . ', '
            . self::tr('name') . ' AS name, '
            . self::tr('description') . ' AS description,
              (SELECT COUNT(*) FROM products p
                WHERE p.category_id = c.id AND p.status = \'published\') AS product_count'
            . self::JOINS . '
             WHERE c.is_active = 1
             ORDER BY c.sort_order ASC, c.id ASC',
            self::langParams($locale)
        );
    }

    public static function findBySlug(string $slug, ?string $locale = null): ?array
    {
        return self::db()->first(
            'SELECT ' . self::SELECT . ', '
            . self::tr('name') . ' AS name, '
            . self::tr('description') . ' AS description, '
            . self::tr('seo_title') . ' AS seo_title, '
            . self::tr('seo_description') . ' AS seo_description'
            . self::JOINS . '
             WHERE c.slug = :slug AND c.is_active = 1
             LIMIT 1',
            self::langParams($locale) + ['slug' => $slug]
        );
    }

    public static function find(int $id): ?array
    {
        return self::db()->first('SELECT * FROM categories WHERE id = :id LIMIT 1', ['id' => $id]);
    }

    /** Every category with all translations, for the admin list and forms. */
    public static function allForAdmin(): array
    {
        $rows = self::db()->all(
            'SELECT c.*, m.path AS image_path,
                    (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS product_count
             FROM categories c
             LEFT JOIN media m ON m.id = c.image_id
             ORDER BY c.sort_order ASC, c.id ASC'
        );

        foreach ($rows as &$row) {
            $row['translations'] = self::translations((int) $row['id']);
        }

        return $rows;
    }

    /** @return array<string,array<string,string>> lang => fields */
    public static function translations(int $id): array
    {
        $rows   = self::db()->all('SELECT * FROM category_translations WHERE category_id = :id', ['id' => $id]);
        $result = [];

        foreach ($rows as $row) {
            $result[$row['lang']] = $row;
        }

        return $result;
    }

    public static function create(array $attributes, array $translations): int
    {
        $id = self::db()->insert('categories', $attributes);
        self::saveTranslations('category_translations', 'category_id', $id, $translations);

        return $id;
    }

    public static function update(int $id, array $attributes, array $translations): void
    {
        if ($attributes !== []) {
            self::db()->update('categories', $attributes, 'id = :id', ['id' => $id]);
        }

        self::saveTranslations('category_translations', 'category_id', $id, $translations);
    }

    public static function delete(int $id): void
    {
        // Products keep existing; the FK sets their category to NULL.
        self::db()->delete('categories', 'id = :id', ['id' => $id]);
    }

    public static function count(): int
    {
        return self::db()->count('SELECT COUNT(*) FROM categories WHERE is_active = 1');
    }

    /** Simple id => name map for <select> inputs. */
    public static function options(?string $locale = null): array
    {
        $rows    = self::published($locale);
        $options = [];

        foreach ($rows as $row) {
            $options[(int) $row['id']] = (string) $row['name'];
        }

        return $options;
    }
}
