<?php
declare(strict_types=1);

namespace App\Models;

final class Page extends Model
{
    /** Section types the front end knows how to render. */
    public const SECTION_TYPES = [
        'hero', 'richtext', 'image_text', 'stats', 'features', 'gallery', 'cta', 'quote',
    ];

    public static function findBySlug(string $slug, ?string $locale = null): ?array
    {
        $page = self::db()->first(
            'SELECT p.id, p.slug, p.status, p.updated_at, '
            . self::tr('title') . ' AS title, '
            . self::tr('subtitle') . ' AS subtitle, '
            . self::tr('body') . ' AS body, '
            . self::tr('seo_title') . ' AS seo_title, '
            . self::tr('seo_description') . ' AS seo_description
             FROM pages p
             LEFT JOIN page_translations t  ON t.page_id  = p.id AND t.lang  = :lang
             LEFT JOIN page_translations tf ON tf.page_id = p.id AND tf.lang = :fallback
             WHERE p.slug = :slug AND p.status = \'published\'
             LIMIT 1',
            self::langParams($locale) + ['slug' => $slug]
        );

        if ($page === null) {
            return null;
        }

        $page['sections'] = self::sections((int) $page['id'], $locale);

        return $page;
    }

    /** @return array<int,array<string,mixed>> */
    public static function sections(int $pageId, ?string $locale = null): array
    {
        $rows = self::db()->all(
            'SELECT s.id, s.type, s.sort_order, s.settings,
                    m.path AS image_path, m.basename AS image_basename, m.variants AS image_variants,
                    m.width AS image_width, m.height AS image_height,
                    m.alt_fa, m.alt_en, m.alt_ar,
                    COALESCE(NULLIF(t.heading, \'\'), NULLIF(tf.heading, \'\'))       AS heading,
                    COALESCE(NULLIF(t.subheading, \'\'), NULLIF(tf.subheading, \'\')) AS subheading,
                    COALESCE(NULLIF(t.body, \'\'), NULLIF(tf.body, \'\'))             AS body,
                    COALESCE(NULLIF(t.cta_label, \'\'), NULLIF(tf.cta_label, \'\'))   AS cta_label,
                    COALESCE(NULLIF(t.cta_url, \'\'), NULLIF(tf.cta_url, \'\'))       AS cta_url
             FROM page_sections s
             LEFT JOIN page_section_translations t  ON t.section_id  = s.id AND t.lang  = :lang
             LEFT JOIN page_section_translations tf ON tf.section_id = s.id AND tf.lang = :fallback
             LEFT JOIN media m                      ON m.id          = s.media_id
             WHERE s.page_id = :page AND s.is_active = 1
             ORDER BY s.sort_order ASC, s.id ASC',
            self::langParams($locale) + ['page' => $pageId]
        );

        foreach ($rows as &$row) {
            $decoded          = json_decode((string) ($row['settings'] ?? ''), true);
            $row['settings']  = is_array($decoded) ? $decoded : [];
        }

        return $rows;
    }

    /** Group a page's sections by type for templates that need direct access. */
    public static function sectionsByType(array $sections): array
    {
        $grouped = [];

        foreach ($sections as $section) {
            $grouped[$section['type']][] = $section;
        }

        return $grouped;
    }

    public static function firstOfType(array $sections, string $type): ?array
    {
        foreach ($sections as $section) {
            if ($section['type'] === $type) {
                return $section;
            }
        }

        return null;
    }

    // --- Admin -------------------------------------------------------------

    public static function allForAdmin(): array
    {
        $rows = self::db()->all(
            'SELECT p.*,
                    (SELECT COUNT(*) FROM page_sections s WHERE s.page_id = p.id) AS section_count
             FROM pages p ORDER BY p.is_system DESC, p.id ASC'
        );

        foreach ($rows as &$row) {
            $row['translations'] = [];
            foreach (self::db()->all('SELECT * FROM page_translations WHERE page_id = :id', ['id' => (int) $row['id']]) as $tr) {
                $row['translations'][$tr['lang']] = $tr;
            }
        }

        return $rows;
    }

    public static function findForAdmin(int $id): ?array
    {
        $page = self::db()->first('SELECT * FROM pages WHERE id = :id LIMIT 1', ['id' => $id]);

        if ($page === null) {
            return null;
        }

        $page['translations'] = [];
        foreach (self::db()->all('SELECT * FROM page_translations WHERE page_id = :id', ['id' => $id]) as $row) {
            $page['translations'][$row['lang']] = $row;
        }

        $page['sections'] = self::adminSections($id);

        return $page;
    }

    private static function adminSections(int $pageId): array
    {
        $sections = self::db()->all(
            'SELECT s.*, m.path AS image_path
             FROM page_sections s
             LEFT JOIN media m ON m.id = s.media_id
             WHERE s.page_id = :id ORDER BY s.sort_order ASC, s.id ASC',
            ['id' => $pageId]
        );

        foreach ($sections as &$section) {
            $decoded             = json_decode((string) ($section['settings'] ?? ''), true);
            $section['settings'] = is_array($decoded) ? $decoded : [];
            $section['translations'] = [];

            foreach (self::db()->all(
                'SELECT * FROM page_section_translations WHERE section_id = :id',
                ['id' => (int) $section['id']]
            ) as $row) {
                $section['translations'][$row['lang']] = $row;
            }
        }

        return $sections;
    }

    public static function updateTranslations(int $id, array $translations): void
    {
        self::saveTranslations('page_translations', 'page_id', $id, $translations);
        self::db()->update('pages', ['updated_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $id]);
    }

    public static function saveSection(int $pageId, ?int $sectionId, array $attributes, array $translations): int
    {
        $db = self::db();

        if ($sectionId !== null && $db->value('SELECT id FROM page_sections WHERE id = :id AND page_id = :p', ['id' => $sectionId, 'p' => $pageId]) !== null) {
            $db->update('page_sections', $attributes, 'id = :id', ['id' => $sectionId]);
        } else {
            $sectionId = $db->insert('page_sections', $attributes + ['page_id' => $pageId]);
        }

        self::saveTranslations('page_section_translations', 'section_id', $sectionId, $translations);

        return $sectionId;
    }

    public static function deleteSection(int $pageId, int $sectionId): void
    {
        self::db()->delete('page_sections', 'id = :id AND page_id = :p', ['id' => $sectionId, 'p' => $pageId]);
    }

    public static function sitemapEntries(): array
    {
        return self::db()->all("SELECT slug, updated_at FROM pages WHERE status = 'published'");
    }

    public static function recentlyUpdated(int $limit = 5): array
    {
        return self::db()->all(
            'SELECT p.slug, p.updated_at, t.title
             FROM pages p
             LEFT JOIN page_translations t ON t.page_id = p.id AND t.lang = :lang
             ORDER BY p.updated_at DESC LIMIT ' . max(1, $limit),
            ['lang' => \App\Core\Lang::default()]
        );
    }
}
