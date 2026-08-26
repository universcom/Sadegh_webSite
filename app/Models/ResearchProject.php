<?php
declare(strict_types=1);

namespace App\Models;

final class ResearchProject extends Model
{
    private const SELECT = 'r.id, r.slug, r.icon, r.sort_order, r.status, r.updated_at,
            m.path AS image_path, m.basename AS image_basename, m.variants AS image_variants,
            m.width AS image_width, m.height AS image_height,
            m.alt_fa, m.alt_en, m.alt_ar';

    private const JOINS = '
        FROM research_projects r
        LEFT JOIN research_project_translations t  ON t.project_id  = r.id AND t.lang  = :lang
        LEFT JOIN research_project_translations tf ON tf.project_id = r.id AND tf.lang = :fallback
        LEFT JOIN media m                          ON m.id          = r.cover_image_id';

    public static function published(?int $limit = null, ?string $locale = null): array
    {
        return self::db()->all(
            'SELECT ' . self::SELECT . ', '
            . self::tr('title') . ' AS title, '
            . self::tr('summary') . ' AS summary'
            . self::JOINS . "
             WHERE r.status = 'published'
             ORDER BY r.sort_order ASC, r.id ASC"
            . ($limit !== null ? ' LIMIT ' . max(1, $limit) : ''),
            self::langParams($locale)
        );
    }

    public static function findBySlug(string $slug, ?string $locale = null): ?array
    {
        $project = self::db()->first(
            'SELECT ' . self::SELECT . ', '
            . self::tr('title') . ' AS title, '
            . self::tr('summary') . ' AS summary, '
            . self::tr('body') . ' AS body, '
            . self::tr('seo_title') . ' AS seo_title, '
            . self::tr('seo_description') . ' AS seo_description'
            . self::JOINS . "
             WHERE r.slug = :slug AND r.status = 'published'
             LIMIT 1",
            self::langParams($locale) + ['slug' => $slug]
        );

        if ($project === null) {
            return null;
        }

        $project['images'] = self::images((int) $project['id']);

        return $project;
    }

    public static function images(int $projectId): array
    {
        return self::db()->all(
            'SELECT m.id, m.path, m.basename, m.variants, m.width, m.height,
                    m.alt_fa, m.alt_en, m.alt_ar
             FROM research_project_images ri
             JOIN media m ON m.id = ri.media_id
             WHERE ri.project_id = :id
             ORDER BY ri.sort_order ASC, ri.id ASC',
            ['id' => $projectId]
        );
    }

    public static function siblings(int $projectId, ?string $locale = null): array
    {
        return self::db()->all(
            'SELECT ' . self::SELECT . ', ' . self::tr('title') . ' AS title, ' . self::tr('summary') . ' AS summary'
            . self::JOINS . "
             WHERE r.status = 'published' AND r.id <> :id
             ORDER BY r.sort_order ASC
             LIMIT 3",
            self::langParams($locale) + ['id' => $projectId]
        );
    }

    public static function sitemapEntries(): array
    {
        return self::db()->all(
            "SELECT slug, updated_at FROM research_projects WHERE status = 'published' ORDER BY sort_order ASC"
        );
    }

    // --- Admin -------------------------------------------------------------

    public static function allForAdmin(): array
    {
        $rows = self::db()->all(
            'SELECT r.*, m.path AS image_path
             FROM research_projects r
             LEFT JOIN media m ON m.id = r.cover_image_id
             ORDER BY r.sort_order ASC, r.id ASC'
        );

        foreach ($rows as &$row) {
            $row['translations'] = [];
            foreach (self::db()->all(
                'SELECT * FROM research_project_translations WHERE project_id = :id',
                ['id' => (int) $row['id']]
            ) as $tr) {
                $row['translations'][$tr['lang']] = $tr;
            }
        }

        return $rows;
    }

    public static function findForAdmin(int $id): ?array
    {
        $project = self::db()->first('SELECT * FROM research_projects WHERE id = :id LIMIT 1', ['id' => $id]);

        if ($project === null) {
            return null;
        }

        $project['translations'] = [];
        foreach (self::db()->all(
            'SELECT * FROM research_project_translations WHERE project_id = :id',
            ['id' => $id]
        ) as $row) {
            $project['translations'][$row['lang']] = $row;
        }

        $project['images'] = self::images($id);

        return $project;
    }

    public static function create(array $attributes, array $translations): int
    {
        $id = self::db()->insert('research_projects', $attributes);
        self::saveTranslations('research_project_translations', 'project_id', $id, $translations);

        return $id;
    }

    public static function update(int $id, array $attributes, array $translations): void
    {
        if ($attributes !== []) {
            self::db()->update('research_projects', $attributes, 'id = :id', ['id' => $id]);
        }

        self::saveTranslations('research_project_translations', 'project_id', $id, $translations);
    }

    public static function delete(int $id): void
    {
        self::db()->delete('research_projects', 'id = :id', ['id' => $id]);
    }

    public static function count(): int
    {
        return self::db()->count("SELECT COUNT(*) FROM research_projects WHERE status = 'published'");
    }
}
