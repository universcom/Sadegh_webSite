<?php
declare(strict_types=1);

namespace Database;

use App\Core\Config;
use App\Core\Database;
use App\Core\Lang;

/**
 * Imports the content extracted from the supplied raw materials.
 *
 * The seeder is idempotent: it keys on slugs, so re-running it updates rather
 * than duplicates. Media rows are matched to the manifest produced by the
 * build-time image pipeline (database/media_manifest.json).
 */
final class Seeder
{
    private Database $db;
    private string $contentPath;
    /** @var array<string,int> manifest key => media id */
    private array $mediaIds = [];
    private array $report   = [];

    public function __construct(Database $db, ?string $basePath = null)
    {
        $this->db          = $db;
        $basePath          = $basePath ?? Config::get('app.base_path', dirname(__DIR__));
        $this->contentPath = $basePath . '/database';
    }

    /** @return array<string,int> counts of what was imported */
    public function run(): array
    {
        $this->db->transaction(function (): void {
            $this->seedMedia();
            $this->seedSettings();
            $this->seedCategories();
            $this->seedProducts();
            $this->seedResearch();
            $this->seedPages();
        });

        return $this->report;
    }

    // -----------------------------------------------------------------------

    private function load(string $file): array
    {
        $path = $this->contentPath . '/content/' . $file . '.php';

        if (!is_file($path)) {
            return [];
        }

        $data = require $path;

        return is_array($data) ? $data : [];
    }

    private function count(string $key, int $by = 1): void
    {
        $this->report[$key] = ($this->report[$key] ?? 0) + $by;
    }

    /** Language codes to write translations for. */
    private function languages(): array
    {
        return Lang::enabled();
    }

    // -----------------------------------------------------------------------

    private function seedMedia(): void
    {
        $path = $this->contentPath . '/media_manifest.json';

        if (!is_file($path)) {
            return;
        }

        $manifest = json_decode((string) file_get_contents($path), true);

        if (!is_array($manifest)) {
            return;
        }

        foreach ($manifest as $key => $entry) {
            $existing = $this->db->value(
                'SELECT id FROM media WHERE path = :path LIMIT 1',
                ['path' => $entry['path']]
            );

            $row = [
                'path'          => $entry['path'],
                'basename'      => $entry['basename'],
                'variants'      => json_encode($entry['variants']),
                'original_name' => basename((string) $entry['source']),
                'mime'          => $entry['mime'],
                'kind'          => 'image',
                'size'          => 0,
                'width'         => $entry['width'],
                'height'        => $entry['height'],
                'alt_fa'        => $entry['alt_fa'],
                'alt_en'        => $entry['alt_en'],
                'alt_ar'        => $entry['alt_ar'],
                'source_ref'    => $entry['source'],
            ];

            if ($existing !== null) {
                $this->db->update('media', $row, 'id = :id', ['id' => (int) $existing]);
                $this->mediaIds[$key] = (int) $existing;
            } else {
                $this->mediaIds[$key] = $this->db->insert('media', $row);
                $this->count('media');
            }
        }
    }

    private function mediaId(?string $key): ?int
    {
        return $key === null ? null : ($this->mediaIds[$key] ?? null);
    }

    // -----------------------------------------------------------------------

    private function seedSettings(): void
    {
        $site = $this->load('site');

        foreach ($site['settings']['neutral'] ?? [] as $group => $entries) {
            foreach ($entries as $key => $value) {
                $this->putSetting($key, '', (string) $value, (string) $group);
            }
        }

        foreach ($site['settings']['translated'] ?? [] as $lang => $entries) {
            if (!Lang::isSupported($lang)) {
                continue;
            }

            foreach ($entries as $key => $value) {
                $this->putSetting($key, $lang, (string) $value, 'general');
            }
        }
    }

    /** Insert a setting, leaving an operator-edited value untouched. */
    private function putSetting(string $key, string $lang, string $value, string $group): void
    {
        $existing = $this->db->value(
            'SELECT id FROM settings WHERE skey = :k AND lang = :l LIMIT 1',
            ['k' => $key, 'l' => $lang]
        );

        if ($existing !== null) {
            return;
        }

        $this->db->insert('settings', [
            'skey'       => $key,
            'lang'       => $lang,
            'svalue'     => $value,
            'group_name' => $group,
        ]);
        $this->count('settings');
    }

    // -----------------------------------------------------------------------

    private function seedCategories(): void
    {
        foreach ($this->load('site')['categories'] ?? [] as $index => $category) {
            $id = $this->upsert('categories', ['slug' => $category['slug']], [
                'slug'       => $category['slug'],
                'image_id'   => $this->mediaId($category['image'] ?? null),
                'sort_order' => $index,
                'is_active'  => 1,
            ]);

            foreach ($this->languages() as $lang) {
                $tr = $category['tr'][$lang] ?? $category['tr']['fa'] ?? [];

                $this->upsertTranslation('category_translations', 'category_id', $id, $lang, [
                    'name'        => $tr['name'] ?? '',
                    'description' => $tr['description'] ?? null,
                ]);
            }

            $this->count('categories');
        }
    }

    // -----------------------------------------------------------------------

    private function seedProducts(): void
    {
        $categoryIds = [];
        foreach ($this->db->all('SELECT id, slug FROM categories') as $row) {
            $categoryIds[$row['slug']] = (int) $row['id'];
        }

        foreach ($this->load('products') as $index => $product) {
            $id = $this->upsert('products', ['slug' => $product['slug']], [
                'slug'           => $product['slug'],
                'category_id'    => $categoryIds[$product['category']] ?? null,
                'model_code'     => $product['model_code'] ?? null,
                'cover_image_id' => $this->mediaId($product['cover'] ?? null),
                'is_featured'    => !empty($product['featured']) ? 1 : 0,
                'status'         => 'published',
                'sort_order'     => $index,
                'needs_review'   => !empty($product['needs_review']) ? 1 : 0,
                'source_ref'     => $product['source_ref'] ?? null,
            ]);

            foreach ($this->languages() as $lang) {
                $tr = $product['tr'][$lang] ?? $product['tr']['fa'] ?? [];

                $this->upsertTranslation('product_translations', 'product_id', $id, $lang, [
                    'name'         => $tr['name'] ?? '',
                    'summary'      => $tr['summary'] ?? null,
                    'description'  => $tr['description'] ?? null,
                    'applications' => $tr['applications'] ?? null,
                    'advantages'   => $tr['advantages'] ?? null,
                ]);
            }

            $this->syncGallery('product_images', 'product_id', $id, $product['gallery'] ?? []);
            $this->syncSpecs($id, $product['specs'] ?? []);
            $this->syncFeatures($id, $product['features'] ?? []);

            $this->count('products');
        }
    }

    private function syncSpecs(int $productId, array $groups): void
    {
        // Specs are fully rewritten on each import: they are derived data, and
        // partial merging would leave stale rows behind.
        $this->db->delete('product_specs', 'product_id = :id', ['id' => $productId]);
        $this->db->delete('product_spec_groups', 'product_id = :id', ['id' => $productId]);

        foreach ($groups as $groupIndex => $group) {
            $groupId = $this->db->insert('product_spec_groups', [
                'product_id' => $productId,
                'sort_order' => $groupIndex,
            ]);

            foreach ($this->languages() as $lang) {
                $title = $group['title'][$lang] ?? $group['title']['fa'] ?? null;

                if ($title !== null) {
                    $this->db->insert('product_spec_group_translations', [
                        'group_id' => $groupId,
                        'lang'     => $lang,
                        'title'    => $title,
                    ]);
                }
            }

            foreach ($group['rows'] as $rowIndex => $row) {
                $specId = $this->db->insert('product_specs', [
                    'product_id' => $productId,
                    'group_id'   => $groupId,
                    'sort_order' => $rowIndex,
                ]);

                foreach ($this->languages() as $lang) {
                    // Flat rows carry one label/value used in every language;
                    // that is how the English datasheets are reproduced exactly.
                    if (isset($row['label'])) {
                        $label = (string) $row['label'];
                        $value = (string) $row['value'];
                    } else {
                        $pair  = $row[$lang] ?? $row['fa'] ?? ['', ''];
                        $label = (string) ($pair[0] ?? '');
                        $value = (string) ($pair[1] ?? '');
                    }

                    if ($label === '' && $value === '') {
                        continue;
                    }

                    $this->db->insert('product_spec_translations', [
                        'spec_id' => $specId,
                        'lang'    => $lang,
                        'label'   => $label,
                        'value'   => $value,
                    ]);
                }

                $this->count('spec_rows');
            }
        }
    }

    private function syncFeatures(int $productId, array $features): void
    {
        $this->db->delete('product_features', 'product_id = :id', ['id' => $productId]);

        foreach ($features as $index => $feature) {
            $featureId = $this->db->insert('product_features', [
                'product_id' => $productId,
                'sort_order' => $index,
            ]);

            foreach ($this->languages() as $lang) {
                $text = $feature[$lang] ?? $feature['fa'] ?? '';

                if ($text === '') {
                    continue;
                }

                $this->db->insert('product_feature_translations', [
                    'feature_id' => $featureId,
                    'lang'       => $lang,
                    'text'       => $text,
                ]);
            }
        }
    }

    // -----------------------------------------------------------------------

    private function seedResearch(): void
    {
        foreach ($this->load('research') as $index => $project) {
            $id = $this->upsert('research_projects', ['slug' => $project['slug']], [
                'slug'           => $project['slug'],
                'cover_image_id' => $this->mediaId($project['cover'] ?? null),
                'icon'           => $project['icon'] ?? null,
                'sort_order'     => $index,
                'status'         => 'published',
                'source_ref'     => $project['source_ref'] ?? null,
            ]);

            foreach ($this->languages() as $lang) {
                $tr = $project['tr'][$lang] ?? $project['tr']['fa'] ?? [];

                $this->upsertTranslation('research_project_translations', 'project_id', $id, $lang, [
                    'title'   => $tr['title'] ?? '',
                    'summary' => $tr['summary'] ?? null,
                    'body'    => $tr['body'] ?? null,
                ]);
            }

            $this->syncGallery('research_project_images', 'project_id', $id, $project['gallery'] ?? []);
            $this->count('research_projects');
        }
    }

    // -----------------------------------------------------------------------

    private function seedPages(): void
    {
        foreach ($this->load('pages') as $slug => $page) {
            $id = $this->upsert('pages', ['slug' => $slug], [
                'slug'      => $slug,
                'is_system' => !empty($page['system']) ? 1 : 0,
                'status'    => 'published',
            ]);

            foreach ($this->languages() as $lang) {
                $tr = $page['tr'][$lang] ?? $page['tr']['fa'] ?? [];

                $this->upsertTranslation('page_translations', 'page_id', $id, $lang, [
                    'title'    => $tr['title'] ?? ucfirst($slug),
                    'subtitle' => $tr['subtitle'] ?? null,
                    'body'     => $tr['body'] ?? null,
                ]);
            }

            // Sections are rewritten wholesale, like specs.
            $this->db->delete('page_sections', 'page_id = :id', ['id' => $id]);

            foreach ($page['sections'] ?? [] as $index => $section) {
                $sectionId = $this->db->insert('page_sections', [
                    'page_id'    => $id,
                    'type'       => $section['type'],
                    'media_id'   => $this->mediaId($section['media'] ?? null),
                    'settings'   => isset($section['settings']) ? json_encode($section['settings']) : null,
                    'sort_order' => $index,
                    'is_active'  => 1,
                ]);

                foreach ($this->languages() as $lang) {
                    $tr = $section['tr'][$lang] ?? $section['tr']['fa'] ?? [];

                    $this->db->insert('page_section_translations', [
                        'section_id' => $sectionId,
                        'lang'       => $lang,
                        'heading'    => $tr['heading'] ?? null,
                        'subheading' => $tr['subheading'] ?? null,
                        'body'       => $tr['body'] ?? null,
                        'cta_label'  => $tr['cta_label'] ?? null,
                        'cta_url'    => $tr['cta_url'] ?? null,
                    ]);
                }

                $this->count('page_sections');
            }

            $this->count('pages');
        }
    }

    // -----------------------------------------------------------------------

    /** Insert or update by a unique key; returns the row id. */
    private function upsert(string $table, array $key, array $data): int
    {
        $column = array_key_first($key);
        $id     = $this->db->value(
            sprintf('SELECT id FROM `%s` WHERE `%s` = :v LIMIT 1', $table, $column),
            ['v' => $key[$column]]
        );

        if ($id !== null) {
            $this->db->update($table, $data, 'id = :id', ['id' => (int) $id]);

            return (int) $id;
        }

        return $this->db->insert($table, $data);
    }

    private function upsertTranslation(string $table, string $foreignKey, int $id, string $lang, array $fields): void
    {
        $existing = $this->db->value(
            sprintf('SELECT id FROM `%s` WHERE `%s` = :id AND lang = :lang LIMIT 1', $table, $foreignKey),
            ['id' => $id, 'lang' => $lang]
        );

        if ($existing !== null) {
            $this->db->update($table, $fields, 'id = :id', ['id' => (int) $existing]);

            return;
        }

        $this->db->insert($table, $fields + [$foreignKey => $id, 'lang' => $lang]);
    }

    private function syncGallery(string $table, string $foreignKey, int $id, array $keys): void
    {
        $this->db->delete($table, sprintf('`%s` = :id', $foreignKey), ['id' => $id]);

        $order = 0;
        foreach ($keys as $key) {
            $mediaId = $this->mediaId($key);

            if ($mediaId === null) {
                continue;
            }

            $this->db->insert($table, [
                $foreignKey  => $id,
                'media_id'   => $mediaId,
                'sort_order' => $order++,
            ]);
        }
    }
}
