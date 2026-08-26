<?php
declare(strict_types=1);

/**
 * Rahyaft Sanat — single public entry point.
 *
 * Every request that is not a real file on disk is routed here by .htaccess
 * (or reaches it as /index.php/path when mod_rewrite is unavailable).
 */

// Fail fast and clearly on an unsupported PHP version, before any modern syntax
// in the rest of the codebase would trigger a parse error.
if (PHP_VERSION_ID < 80100) {
    http_response_code(500);
    header('Content-Type: text/html; charset=UTF-8');
    exit('<!doctype html><meta charset="utf-8"><h1>PHP 8.1 or newer is required</h1>'
        . '<p>This server runs PHP ' . htmlspecialchars(PHP_VERSION, ENT_QUOTES) . '.</p>');
}

$basePath = __DIR__;

require $basePath . '/app/Core/Autoloader.php';

App\Core\Autoloader::register($basePath);
require $basePath . '/app/Support/helpers.php';

$app = (new App\Core\App($basePath))->boot();

// Not installed yet: send the operator to the wizard instead of a fatal error.
if (!$app->isInstalled()) {
    if (is_file($basePath . '/install.php')) {
        header('Location: ' . App\Core\Url::to('install.php'), true, 302);
        exit;
    }

    http_response_code(503);
    header('Content-Type: text/html; charset=UTF-8');
    exit('<!doctype html><meta charset="utf-8"><title>Not installed</title>'
        . '<p style="font-family:system-ui;padding:40px">The site is not installed yet, '
        . 'and the installer is missing. Restore <code>install.php</code> or upload a valid '
        . '<code>.env</code> file.</p>');
}

$app->run();
