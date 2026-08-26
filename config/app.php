<?php
declare(strict_types=1);

use App\Core\Env;

$basePath = dirname(__DIR__);

return [
    'env'   => (string) Env::get('APP_ENV', 'production'),
    'debug' => Env::bool('APP_DEBUG', false),
    'url'   => rtrim((string) Env::get('APP_URL', ''), '/'),
    'key'   => (string) Env::get('APP_KEY', ''),

    'default_locale' => (string) Env::get('APP_DEFAULT_LOCALE', 'fa'),
    'locales'        => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) Env::get('APP_LOCALES', 'fa,en,ar'))
    ))),

    'base_path'    => $basePath,
    'storage_path' => $basePath . '/storage',
    'uploads_path' => $basePath . '/uploads',

    'timezone' => 'Asia/Tehran',
];
