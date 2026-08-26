<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Product extends Model
{
    private const CARD_SELECT = 'p.id, p.slug, p.model_code, p.is_featured, p.status, p.sort_order,
            p.category_id,
            m.path AS image_path, m.basename AS image_basename, m.variants AS image_variants,
            m.width AS image_width, m.height AS image_height,
            m.alt_fa, m.alt_en, m.alt_ar';

    private const CARD_JOINS = '
        FROM products p
        LEFT JOIN product_translations t  ON t.product_id  = p.id AND t.lang  = :lang
        LEFT JOIN product_translations tf ON tf.product_id = p.id AND tf.lang = :fallback
        LEFT JOIN media m                 ON m.id          = p.cover_image_id';

    private static function cardFields(): string
    {
        return self::CARD_SELECT . ', '
            . self::tr('name') . ' AS name, '
            . self::tr('summary') . ' AS summary';
    }

    /**
     * Paginated, filterable listing.
     *
     * @param array{category?:string,search?:string,sort?:string} $filters
     * @return array{items:array,total:int,pages:int,page:int}
     */
    public static function listing(array $filters = [], int $page = 1, int $perPage = 12, ?string $locale = null): array
    {
        $params = self::langParams($locale);
        $where  = ["p.status = 'published'"];

        if (!empty($filters['category'])) {
            $where[]              = 'c.slug = :category';
            $params['category']   = $filters['category'];
        }

        if (!empty($filters['search'])) {
            // Matches translated name/summary in any language plus the model code,
            // so an English model number finds the product on the Persian site.
            $where[] = '(EXISTS (
                            SELECT 1 FROM product_translations st
                            WHERE st.product_id = p.id
                              AND (st.name LIKE :search OR st.summary LIKE :search)
                        ) OR p.model_code LIKE :search)';
            $params['search'] = '%' . self::escapeLike((string) $filters['search']) . '%';
        }

        $order = match ($filters['sort'] ?? '') {
            'newest'  => 'p.created_at DESC, p.id DESC',
            'name'    => 'name ASC',
            default   => 'p.is_featured DESC, p.sort_order ASC, p.id ASC',
        };

        $clause = implode(' AND ', $where);
        $join   = self::CARD_JOINS . ' LEFT JOIN categories c ON c.id = p.category_id';

        $total   = self::db()->count('SELECT COUNT(*) ' . $join . ' WHERE ' . $clause, $params);
        $perPage = max(1, $perPage);
        $pages   = max(1, (int) ceil($total / $perPage));
        $page    = min(max(1, $page), $pages);

        $items = self::db()->all(
            'SELECT ' . self::cardFields() . $join
            . ' WHERE ' . $clause
            . ' ORDER BY ' . $order
            . ' LIMIT ' . $perPage . ' OFFSET ' . (($page - 1) * $perPage),
            $params
        );

        return ['items' => $items, 'total' => $total, 'pages' => $pages, 'page' => $page];
    }

    /** Featured products for the home page. */
    public static function featured(int $limit = 6, ?string $locale = null): array
    {
        return self::db()->all(
            'SELECT ' . self::cardFields() . self::CARD_JOINS . "
             WHERE p.status = 'published' AND p.is_featured = 1
             ORDER BY p.sort_order ASC, p.id ASC
             LIMIT " . max(1, $limit),
            self::langParams($locale)
        );
    }

    public static function latest(int $limit = 4, ?string $locale = null): array
    {
        return self::db()->all(
            'SELECT ' . self::cardFields() . self::CARD_JOINS . "
             WHERE p.status = 'published'
             ORDER BY p.created_at DESC, p.id DESC
             LIMIT " . max(1, $limit),
            self::langParams($locale)
        );
    }

    /** Full product detail with every related collection eagerly loaded. */
    public static function findBySlug(string $slug, ?string $locale = null): ?array
    {
        $product = self::db()->first(
            'SELECT ' . self::CARD_SELECT . ', p.needs_review, p.created_at, p.updated_at, '
            . self::tr('name') . ' AS name, '
            . self::tr('summary') . ' AS summary, '
            . self::tr('description') . ' AS description, '
            . self::tr('applications') . ' AS applications, '
            . self::tr('advantages') . ' AS advantages, '
            . self::tr('seo_title') . ' AS seo_title, '
            . self::tr('seo_description') . ' AS seo_description,
              c.slug AS category_slug, '
            . 'COALESCE(NULLIF(ct.name, \'\'), NULLIF(ctf.name, \'\')) AS category_name'
            . self::CARD_JOINS . '
              LEFT JOIN categories c              ON c.id           = p.category_id
              LEFT JOIN category_translations ct  ON ct.category_id = c.id AND ct.lang  = :lang
              LEFT JOIN category_translations ctf ON ctf.category_id= c.id AND ctf.lang = :fallback
             WHERE p.slug = :slug AND p.status = \'published\'
             LIMIT 1',
            self::langParams($locale) + ['slug' => $slug]
        );

        if ($product === null) {
            return null;
        }

        $id                     = (int) $product['id'];
        $product['images']      = self::images($id, $locale);
        $product['specGroups']  = self::specGroups($id, $locale);
        $product['features']    = self::features($id, $locale);
        $product['downloads']   = self::downloads($id, $locale);

        return $product;
    }

    /** @return array<int,array<string,mixed>> */
    public static function images(int $productId, ?string $locale = null): array
    {
        return self::db()->all(
            'SELECT m.id, m.path, m.basename, m.variants, m.width, m.height,
                    m.alt_fa, m.alt_en, m.alt_ar, pi.sort_order
             FROM product_images pi
             JOIN media m ON m.id = pi.media_id
             WHERE pi.product_id = :id
             ORDER BY pi.sort_order ASC, pi.id ASC',
            ['id' => $productId]
        );
    }

    /**
     * Specification tables grouped for display.
     *
     * @return array<int,array{title:?string,rows:array<int,array{label:string,value:string}>}>
     */
    public static function specGroups(int $productId, ?string $locale = null): array
    {
        $rows = self::db()->all(
            'SELECT s.id, s.group_id, s.sort_order,
                    COALESCE(NULLIF(st.label, \'\'), NULLIF(stf.label, \'\')) AS label,
                    COALESCE(NULLIF(st.value, \'\'), NULLIF(stf.value, \'\')) AS value,
                    COALESCE(NULLIF(gt.title, \'\'), NULLIF(gtf.title, \'\')) AS group_title,
                    g.sort_order AS group_order
             FROM product_specs s
             LEFT JOIN product_spec_translations st  ON st.spec_id  = s.id AND st.lang  = :lang
             LEFT JOIN product_spec_translations stf ON stf.spec_id = s.id AND stf.lang = :fallback
             LEFT JOIN product_spec_groups g         ON g.id        = s.group_id
             LEFT JOIN product_spec_group_translations gt  ON gt.group_id  = g.id AND gt.lang  = :lang
             LEFT JOIN product_spec_group_translations gtf ON gtf.group_id = g.id AND gtf.lang = :fallback
             WHERE s.product_id = :id
             ORDER BY COALESCE(g.sort_order, 0) ASC, s.sort_order ASC, s.id ASC',
            self::langParams($locale) + ['id' => $productId]
        );

        $groups = [];

        foreach ($rows as $row) {
            if (($row['label'] ?? '') === '' && ($row['value'] ?? '') === '') {
                continue;
            }

            $key = $row['group_id'] === null ? '_' : (string) $row['group_id'];

            $groups[$key]['title']  ??= $row['group_title'];
            $groups[$key]['rows'][] = [
                'label' => (string) $row['label'],
                'value' => (string) $row['value'],
            ];
        }

        return array_values($groups);
    }

    /** @return array<int,string> */
    public static function features(int $productId, ?string $locale = null): array
    {
        $rows = self::db()->all(
            'SELECT COALESCE(NULLIF(ft.text, \'\'), NULLIF(ftf.text, \'\')) AS text
             FROM product_features f
             LEFT JOIN product_feature_translations ft  ON ft.feature_id  = f.id AND ft.lang  = :lang
             LEFT JOIN product_feature_translations ftf ON ftf.feature_id = f.id AND ftf.lang = :fallback
             WHERE f.product_id = :id
             ORDER BY f.sort_order ASC, f.id ASC',
            self::langParams($locale) + ['id' => $productId]
        );

        return array_values(array_filter(
            array_map(static fn (array $r): string => (string) ($r['text'] ?? ''), $rows),
            static fn (string $t): bool => $t !== ''
        ));
    }

    public static function downloads(int $productId, ?string $locale = null): array
    {
        $locale = $locale ?? \App\Core\Lang::current();
        $column = in_array($locale, ['fa', 'en', 'ar'], true) ? 'title_' . $locale : 'title_fa';

        return self::db()->all(
            'SELECT d.id, m.path, m.mime, m.size, m.original_name,
                    COALESCE(NULLIF(d.`' . $column . '`, \'\'), NULLIF(d.title_fa, \'\'), m.original_name) AS title
             FROM product_downloads d
             JOIN media m ON m.id = d.media_id
             WHERE d.product_id = :id
             ORDER BY d.sort_order ASC, d.id ASC',
            ['id' => $productId]
        );
    }

    /** Same-category products, falling back to featured ones if too few. */
    public static function related(int $productId, ?int $categoryId, int $limit = 3, ?string $locale = null): array
    {
        $params = self::langParams($locale) + ['id' => $productId];
        $items  = [];

        if ($categoryId !== null) {
            $params['category'] = $categoryId;
            $items = self::db()->all(
                'SELECT ' . self::cardFields() . self::CARD_JOINS . "
                 WHERE p.status = 'published' AND p.id <> :id AND p.category_id = :category
                 ORDER BY p.sort_order ASC, p.id ASC
                 LIMIT " . max(1, $limit),
                $params
            );
        }

        if (count($items) >= $limit) {
            return $items;
        }

        $seen = array_map(static fn (array $i): int => (int) $i['id'], $items);
        $seen[] = $productId;
        [$placeholders, $values] = Database::inClause($seen);

        $extra = self::db()->all(
            'SELECT ' . self::cardFields() . self::CARD_JOINS . "
             WHERE p.status = 'published' AND p.id NOT IN ($placeholders)
             ORDER BY p.is_featured DESC, p.sort_order ASC
             LIMIT " . max(1, $limit - count($items)),
            array_merge(self::langParams($locale), $values)
        );

        return array_merge($items, $extra);
    }

    /** Slugs + timestamps for the XML sitemap. */
    public static function sitemapEntries(): array
    {
        return self::db()->all(
            "SELECT slug, updated_at FROM products WHERE status = 'published' ORDER BY sort_order ASC"
        );
    }

    // --- Admin -------------------------------------------------------------

    public static function adminListing(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $params = ['lang' => \App\Core\Lang::default()];
        $where  = ['1 = 1'];

        if (!empty($filters['status'])) {
            $where[]           = 'p.status = :status';
            $params['status']  = $filters['status'];
        }

        if (!empty($filters['category'])) {
            $where[]            = 'p.category_id = :category';
            $params['category'] = (int) $filters['category'];
        }

        if (!empty($filters['search'])) {
            $where[] = '(EXISTS (SELECT 1 FROM product_translations st
                                 WHERE st.product_id = p.id AND st.name LIKE :search)
                         OR p.slug LIKE :search OR p.model_code LIKE :search)';
            $params['search'] = '%' . self::escapeLike((string) $filters['search']) . '%';
        }

        $clause = implode(' AND ', $where);

        $total   = self::db()->count(
            'SELECT COUNT(*) FROM products p WHERE ' . $clause,
            $params
        );
        $pages   = max(1, (int) ceil($total / max(1, $perPage)));
        $page    = min(max(1, $page), $pages);

        $items = self::db()->all(
            'SELECT p.id, p.slug, p.status, p.is_featured, p.sort_order, p.model_code,
                    p.needs_review, p.updated_at,
                    t.name AS name, m.path AS image_path,
                    ct.name AS category_name
             FROM products p
             LEFT JOIN product_translations t   ON t.product_id   = p.id AND t.lang = :lang
             LEFT JOIN media m                  ON m.id           = p.cover_image_id
             LEFT JOIN categories c             ON c.id           = p.category_id
             LEFT JOIN category_translations ct ON ct.category_id = c.id AND ct.lang = :lang
             WHERE ' . $clause . '
             ORDER BY p.sort_order ASC, p.id DESC
             LIMIT ' . max(1, $perPage) . ' OFFSET ' . (($page - 1) * max(1, $perPage)),
            $params
        );

        return ['items' => $items, 'total' => $total, 'pages' => $pages, 'page' => $page];
    }

    public static function findForAdmin(int $id): ?array
    {
        $product = self::db()->first('SELECT * FROM products WHERE id = :id LIMIT 1', ['id' => $id]);

        if ($product === null) {
            return null;
        }

        $product['translations'] = [];
        foreach (self::db()->all('SELECT * FROM product_translations WHERE product_id = :id', ['id' => $id]) as $row) {
            $product['translations'][$row['lang']] = $row;
        }

        $product['images']    = self::images($id);
        $product['downloads'] = self::db()->all(
            'SELECT d.*, m.path, m.original_name, m.mime, m.size
             FROM product_downloads d JOIN media m ON m.id = d.media_id
             WHERE d.product_id = :id ORDER BY d.sort_order ASC',
            ['id' => $id]
        );

        // Specs and features with every translation, for the multilingual editor.
        $product['specs']    = self::adminSpecs($id);
        $product['features'] = self::adminFeatures($id);

        return $product;
    }

    private static function adminSpecs(int $productId): array
    {
        $groups = self::db()->all(
            'SELECT g.id, g.sort_order FROM product_spec_groups g
             WHERE g.product_id = :id ORDER BY g.sort_order ASC, g.id ASC',
            ['id' => $productId]
        );

        foreach ($groups as &$group) {
            $group['titles'] = [];
            foreach (self::db()->all(
                'SELECT lang, title FROM product_spec_group_translations WHERE group_id = :id',
                ['id' => (int) $group['id']]
            ) as $row) {
                $group['titles'][$row['lang']] = $row['title'];
            }

            $group['rows'] = [];
            foreach (self::db()->all(
                'SELECT id, sort_order FROM product_specs WHERE group_id = :id ORDER BY sort_order ASC, id ASC',
                ['id' => (int) $group['id']]
            ) as $spec) {
                $spec['values'] = [];
                foreach (self::db()->all(
                    'SELECT lang, label, value FROM product_spec_translations WHERE spec_id = :id',
                    ['id' => (int) $spec['id']]
                ) as $tr) {
                    $spec['values'][$tr['lang']] = ['label' => $tr['label'], 'value' => $tr['value']];
                }
                $group['rows'][] = $spec;
            }
        }

        return $groups;
    }

    private static function adminFeatures(int $productId): array
    {
        $features = self::db()->all(
            'SELECT id, sort_order FROM product_features WHERE product_id = :id ORDER BY sort_order ASC, id ASC',
            ['id' => $productId]
        );

        foreach ($features as &$feature) {
            $feature['texts'] = [];
            foreach (self::db()->all(
                'SELECT lang, text FROM product_feature_translations WHERE feature_id = :id',
                ['id' => (int) $feature['id']]
            ) as $row) {
                $feature['texts'][$row['lang']] = $row['text'];
            }
        }

        return $features;
    }

    public static function create(array $attributes, array $translations): int
    {
        $id = self::db()->insert('products', $attributes);
        self::saveTranslations('product_translations', 'product_id', $id, $translations);

        return $id;
    }

    public static function update(int $id, array $attributes, array $translations): void
    {
        if ($attributes !== []) {
            self::db()->update('products', $attributes, 'id = :id', ['id' => $id]);
        }

        self::saveTranslations('product_translations', 'product_id', $id, $translations);
    }

    public static function delete(int $id): void
    {
        // Cascades clear translations, images, specs, features and downloads.
        self::db()->delete('products', 'id = :id', ['id' => $id]);
    }

    public static function setStatus(int $id, string $status): void
    {
        if (!in_array($status, ['published', 'draft', 'archived'], true)) {
            return;
        }

        self::db()->update('products', ['status' => $status], 'id = :id', ['id' => $id]);
    }

    public static function count(?string $status = 'published'): int
    {
        if ($status === null) {
            return self::db()->count('SELECT COUNT(*) FROM products');
        }

        return self::db()->count('SELECT COUNT(*) FROM products WHERE status = :s', ['s' => $status]);
    }

    /** Escape LIKE wildcards so a literal % or _ in a search term is safe. */
    private static function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $value);
    }
}
