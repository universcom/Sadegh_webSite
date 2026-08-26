<?php
declare(strict_types=1);

use App\Core\Env;

return [
    'mailer'       => (string) Env::get('MAIL_MAILER', 'mail'),
    'host'         => (string) Env::get('MAIL_HOST', ''),
    'port'         => Env::int('MAIL_PORT', 587),
    'encryption'   => (string) Env::get('MAIL_ENCRYPTION', 'tls'),
    'username'     => (string) Env::get('MAIL_USERNAME', ''),
    'password'     => (string) Env::get('MAIL_PASSWORD', ''),
    'from_address' => (string) Env::get('MAIL_FROM_ADDRESS', 'no-reply@localhost'),
    'from_name'    => (string) Env::get('MAIL_FROM_NAME', 'Rahyaft Sanat'),
    'notify_to'    => (string) Env::get('MAIL_NOTIFY_TO', ''),
];
