<?php
/**
 * Responsive image with srcset, lazy loading and an explicit aspect ratio so
 * the layout never shifts while images load.
 *
 * @var array  $media    row with path/basename/variants/width/height/alt_*
 * @var string $sizes
 * @var string $class
 * @var string $fallback alt text when the media row has none
 * @var bool   $eager    true for above-the-fold images
 */

use App\Core\Url;
use App\Models\Media;

$path = (string) ($media['path'] ?? ($media['image_path'] ?? ''));

if ($path === '') {
    return;
}

$normalised = [
    'basename' => $media['basename'] ?? ($media['image_basename'] ?? null),
    'variants' => $media['variants'] ?? ($media['image_variants'] ?? null),
    'path'     => $path,
];

$srcset = media_srcset($normalised);
$alt    = Media::alt($media, (string) ($fallback ?? ''));
$width  = (int) ($media['width'] ?? ($media['image_width'] ?? 0));
$height = (int) ($media['height'] ?? ($media['image_height'] ?? 0));
$eager  = (bool) ($eager ?? false);
?>
<img src="<?= e(Url::upload($path)) ?>"
     <?php if ($srcset !== ''): ?>srcset="<?= e($srcset) ?>"<?php endif; ?>
     <?php if ($srcset !== ''): ?>sizes="<?= e($sizes ?? '(max-width: 768px) 100vw, 33vw') ?>"<?php endif; ?>
     alt="<?= e($alt) ?>"
     <?php if ($width > 0 && $height > 0): ?>width="<?= $width ?>" height="<?= $height ?>"<?php endif; ?>
     <?php if ($eager): ?>fetchpriority="high"<?php else: ?>loading="lazy" decoding="async"<?php endif; ?>
     <?php if (!empty($class)): ?>class="<?= e($class) ?>"<?php endif; ?>>
