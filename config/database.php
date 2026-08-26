<?php
declare(strict_types=1);

use App\Core\Env;

return [
    'host'     => (string) Env::get('DB_HOST', 'localhost'),
    'port'     => Env::int('DB_PORT', 3306),
    'name'     => (string) Env::get('DB_NAME', ''),
    'user'     => (string) Env::get('DB_USER', ''),
    'password' => (string) Env::get('DB_PASSWORD', ''),
    'charset'  => (string) Env::get('DB_CHARSET', 'utf8mb4'),
];
