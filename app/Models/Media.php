<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Lang;
use App\Core\Uploader;

final class Media extends Model
{
    public static function create(array $data): int
    {
        return self::db()->insert('media', $data);
    }

    /** Persist an uploaded image and return its media id. */
    public static function storeImage(array $file, array $alt = []): int
    {
        $stored = Uploader::image($file, 'media');

        return self::create([
            'path'          => $stored['path'],
            'basename'      => $stored['basename'],
            'variants'      => json_encode($stored['variants']),
            'original_name' => $stored['original_name'],
            'mime'          => $stored['mime'],
            'kind'          => 'image',
            'size'          => $stored['size'],
            'width'         => $stored['width'],
            'height'        => $stored['height'],
            'alt_fa'        => $alt['fa'] ?? null,
            'alt_en'        => $alt['en'] ?? null,
            'alt_ar'        => $alt['ar'] ?? null,
        ]);
    }

    public static function storeDocument(array $file): int
    {
        $stored = Uploader::document($file, 'files');

        return self::create([
            'path'          => $stored['path'],
            'original_name' => $stored['original_name'],
            'mime'          => $stored['mime'],
            'kind'          => 'document',
            'size'          => $stored['size'],
        ]);
    }

    public static function find(int $id): ?array
    {
        return self::db()->first('SELECT * FROM media WHERE id = :id LIMIT 1', ['id' => $id]);
    }

    /**
     * @return array{items:array,total:int,pages:int,page:int}
     */
    public static function listing(string $kind = '', int $page = 1, int $perPage = 24, string $search = ''): array
    {
        $where  = ['1 = 1'];
        $params = [];

        if (in_array($kind, ['image', 'document'], true)) {
            $where[]        = 'kind = :kind';
            $params['kind'] = $kind;
        }

        if ($search !== '') {
            $where[] = '(original_name LIKE :q OR alt_fa LIKE :q OR alt_en LIKE :q OR alt_ar LIKE :q)';
            $params['q'] = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $search) . '%';
        }

        $clause  = implode(' AND ', $where);
        $total   = self::db()->count('SELECT COUNT(*) FROM media WHERE ' . $clause, $params);
        $perPage = max(1, $perPage);
        $pages   = max(1, (int) ceil($total / $perPage));
        $page    = min(max(1, $page), $pages);

        return [
            'items' => self::db()->all(
                'SELECT * FROM media WHERE ' . $clause . '
                 ORDER BY created_at DESC, id DESC
                 LIMIT ' . $perPage . ' OFFSET ' . (($page - 1) * $perPage),
                $params
            ),
            'total' => $total,
            'pages' => $pages,
            'page'  => $page,
        ];
    }

    public static function updateAlt(int $id, array $alt): void
    {
        self::db()->update('media', [
            'alt_fa' => $alt['fa'] ?? null,
            'alt_en' => $alt['en'] ?? null,
            'alt_ar' => $alt['ar'] ?? null,
        ], 'id = :id', ['id' => $id]);
    }

    /**
     * Count every reference to a media row, so the admin can warn before a
     * delete instead of silently breaking a page.
     */
    public static function usageCount(int $id): int
    {
        $queries = [
            'SELECT COUNT(*) FROM products WHERE cover_image_id = :id',
            'SELECT COUNT(*) FROM product_images WHERE media_id = :id',
            'SELECT COUNT(*) FROM product_downloads WHERE media_id = :id',
            'SELECT COUNT(*) FROM categories WHERE image_id = :id',
            'SELECT COUNT(*) FROM research_projects WHERE cover_image_id = :id',
            'SELECT COUNT(*) FROM research_project_images WHERE media_id = :id',
            'SELECT COUNT(*) FROM page_sections WHERE media_id = :id',
        ];

        $total = 0;
        foreach ($queries as $sql) {
            $total += self::db()->count($sql, ['id' => $id]);
        }

        return $total;
    }

    /** Remove a media row and its files. Refuses while it is still in use. */
    public static function delete(int $id, bool $force = false): bool
    {
        $media = self::find($id);

        if ($media === null) {
            return false;
        }

        if (!$force && self::usageCount($id) > 0) {
            return false;
        }

        Uploader::delete((string) $media['path']);
        self::db()->delete('media', 'id = :id', ['id' => $id]);

        return true;
    }

    /** Alt text in the active language, falling back across the others. */
    public static function alt(array $media, string $fallback = ''): string
    {
        foreach ([Lang::current(), Lang::default(), 'en', 'fa', 'ar'] as $code) {
            $value = $media['alt_' . $code] ?? null;
            if (is_string($value) && trim($value) !== '') {
                return $value;
            }
        }

        return $fallback;
    }

    public static function count(): int
    {
        return self::db()->count('SELECT COUNT(*) FROM media');
    }

    public static function humanSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $index = 0;

        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return Lang::digits(number_format($bytes, $index === 0 ? 0 : 1)) . ' ' . $units[$index];
    }
}
