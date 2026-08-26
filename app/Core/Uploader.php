<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Validated file uploads.
 *
 * Defence in depth: the extension must be allow-listed, the sniffed MIME type
 * must match that extension, images must actually decode, the stored filename
 * is randomly generated (the client name is never used on disk), and the
 * uploads directory refuses to execute anything (see uploads/.htaccess).
 */
final class Uploader
{
    /** extension => [allowed mime types] */
    private const IMAGE_TYPES = [
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png'  => ['image/png'],
        'gif'  => ['image/gif'],
        'webp' => ['image/webp'],
    ];

    private const DOCUMENT_TYPES = [
        'pdf'  => ['application/pdf'],
        'zip'  => ['application/zip', 'application/x-zip-compressed'],
        'doc'  => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'xls'  => ['application/vnd.ms-excel'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
    ];

    private const MAX_IMAGE_BYTES    = 8388608;   // 8 MB
    private const MAX_DOCUMENT_BYTES = 16777216;  // 16 MB
    private const IMAGE_WIDTHS       = [1600, 1200, 800, 400];

    /**
     * Store an uploaded image and generate responsive variants.
     *
     * @return array{path:string,basename:string,variants:array<int,int>,width:int,height:int,mime:string,size:int,original_name:string}
     */
    public static function image(array $file, string $subdirectory = 'media'): array
    {
        $extension = self::guard($file, self::IMAGE_TYPES, self::MAX_IMAGE_BYTES);

        // getimagesize() only succeeds on a real, decodable image.
        $info = @getimagesize($file['tmp_name']);
        if ($info === false || (int) $info[0] < 1 || (int) $info[1] < 1) {
            throw new RuntimeException(Lang::get('upload.not_an_image'));
        }

        [$width, $height] = $info;
        $mime             = (string) $info['mime'];

        $basename  = self::uniqueName();
        $directory = self::directory($subdirectory);

        $variants = [];
        $source   = self::openImage($file['tmp_name'], $mime);

        if ($source !== null) {
            foreach (self::IMAGE_WIDTHS as $target) {
                if ($target > $width && $target !== self::IMAGE_WIDTHS[0]) {
                    continue;
                }

                $targetWidth  = min($target, $width);
                $targetHeight = max(1, (int) round($height * $targetWidth / $width));
                $resized      = imagecreatetruecolor($targetWidth, $targetHeight);

                // Flatten transparency onto white; every variant is written as JPEG.
                $white = imagecolorallocate($resized, 255, 255, 255);
                imagefilledrectangle($resized, 0, 0, $targetWidth, $targetHeight, $white);
                imagecopyresampled($resized, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
                imagejpeg($resized, sprintf('%s/%s-%d.jpg', $directory, $basename, $target), 82);

                // imagedestroy() has been a no-op since PHP 8.0 and is deprecated in
                // 8.5; the GdImage is freed when the variable is released.
                unset($resized);

                $variants[] = $target;
            }

            unset($source);
        }

        if ($variants === []) {
            // GD unavailable or the format is unsupported — keep the original.
            $stored = sprintf('%s/%s.%s', $directory, $basename, $extension);
            self::move($file['tmp_name'], $stored);

            return [
                'path'          => sprintf('%s/%s.%s', $subdirectory, $basename, $extension),
                'basename'      => $basename,
                'variants'      => [],
                'width'         => (int) $width,
                'height'        => (int) $height,
                'mime'          => $mime,
                'size'          => (int) $file['size'],
                'original_name' => self::safeOriginalName($file['name']),
            ];
        }

        $largest = $variants[0];

        return [
            'path'          => sprintf('%s/%s-%d.jpg', $subdirectory, $basename, $largest),
            'basename'      => $basename,
            'variants'      => $variants,
            'width'         => min($largest, (int) $width),
            'height'        => (int) round($height * min($largest, (int) $width) / $width),
            'mime'          => 'image/jpeg',
            'size'          => (int) (@filesize(sprintf('%s/%s-%d.jpg', $directory, $basename, $largest)) ?: $file['size']),
            'original_name' => self::safeOriginalName($file['name']),
        ];
    }

    /**
     * Store a document (datasheet, catalogue).
     *
     * @return array{path:string,mime:string,size:int,original_name:string}
     */
    public static function document(array $file, string $subdirectory = 'files'): array
    {
        $extension = self::guard($file, self::DOCUMENT_TYPES, self::MAX_DOCUMENT_BYTES);

        $basename = self::uniqueName();
        $stored   = sprintf('%s/%s.%s', self::directory($subdirectory), $basename, $extension);

        self::move($file['tmp_name'], $stored);

        return [
            'path'          => sprintf('%s/%s.%s', $subdirectory, $basename, $extension),
            'mime'          => (string) (@mime_content_type($stored) ?: 'application/octet-stream'),
            'size'          => (int) $file['size'],
            'original_name' => self::safeOriginalName($file['name']),
        ];
    }

    public static function wasUploaded(?array $file): bool
    {
        return $file !== null
            && isset($file['error'])
            && $file['error'] !== UPLOAD_ERR_NO_FILE
            && ($file['size'] ?? 0) > 0;
    }

    /** Delete a stored file and every generated variant. */
    public static function delete(string $relativePath): void
    {
        $root = Config::get('app.uploads_path');
        $full = realpath($root . '/' . ltrim($relativePath, '/'));

        // Never delete outside the uploads root.
        if ($full === false || !str_starts_with($full, (string) realpath($root))) {
            return;
        }

        @unlink($full);

        // Remove sibling responsive variants: name-1600.jpg, name-800.jpg, ...
        if (preg_match('/^(.*)-\d+\.jpg$/', $full, $m)) {
            foreach (glob($m[1] . '-*.jpg') ?: [] as $variant) {
                @unlink($variant);
            }
        }
    }

    // --- Internals ----------------------------------------------------------

    private static function guard(array $file, array $allowed, int $maxBytes): string
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new RuntimeException(Lang::get('upload.invalid'));
        }

        match ($file['error']) {
            UPLOAD_ERR_OK        => null,
            UPLOAD_ERR_INI_SIZE,
            UPLOAD_ERR_FORM_SIZE => throw new RuntimeException(Lang::get('upload.too_large')),
            UPLOAD_ERR_NO_FILE   => throw new RuntimeException(Lang::get('upload.none')),
            default              => throw new RuntimeException(Lang::get('upload.failed')),
        };

        if (!is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException(Lang::get('upload.invalid'));
        }

        if ((int) $file['size'] > $maxBytes) {
            throw new RuntimeException(Lang::get('upload.too_large'));
        }

        $extension = strtolower((string) pathinfo((string) $file['name'], PATHINFO_EXTENSION));

        if (!isset($allowed[$extension])) {
            throw new RuntimeException(Lang::get('upload.extension'));
        }

        // The claimed extension must agree with the sniffed content type.
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = (string) $finfo->file($file['tmp_name']);

        if (!in_array($mime, $allowed[$extension], true)) {
            throw new RuntimeException(Lang::get('upload.mime'));
        }

        return $extension;
    }

    private static function openImage(string $path, string $mime): ?\GdImage
    {
        if (!function_exists('imagecreatetruecolor')) {
            return null;
        }

        $image = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png'  => @imagecreatefrompng($path),
            'image/gif'  => @imagecreatefromgif($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default      => false,
        };

        return $image instanceof \GdImage ? $image : null;
    }

    private static function uniqueName(): string
    {
        return date('Ymd') . '-' . bin2hex(random_bytes(8));
    }

    private static function directory(string $subdirectory): string
    {
        // Only known sub-directories, never anything derived from input.
        if (!in_array($subdirectory, ['media', 'files', 'thumbs'], true)) {
            throw new RuntimeException(Lang::get('upload.invalid'));
        }

        $path = Config::get('app.uploads_path') . '/' . $subdirectory;

        if (!is_dir($path) && !@mkdir($path, 0o755, true) && !is_dir($path)) {
            throw new RuntimeException(Lang::get('upload.directory'));
        }

        return $path;
    }

    private static function move(string $from, string $to): void
    {
        if (!move_uploaded_file($from, $to)) {
            throw new RuntimeException(Lang::get('upload.failed'));
        }

        @chmod($to, 0o644);
    }

    private static function safeOriginalName(string $name): string
    {
        $name = basename($name);
        $name = (string) preg_replace('/[^\p{L}\p{N}\s._-]+/u', '', $name);

        return mb_substr(trim($name), 0, 190);
    }
}
