<?php
declare(strict_types=1);

/**
 * Router for PHP's built-in development server ONLY:
 *
 *   php -S localhost:8000 server.php
 *
 * Production uses Apache + .htaccess and never touches this file. It refuses to
 * run under any other SAPI so that uploading it to a host is harmless.
 */

if (PHP_SAPI !== 'cli-server') {
    http_response_code(404);
    exit;
}

$path = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';
$path = rawurldecode($path);

// Refuse the paths .htaccess blocks in production, so local behaviour matches.
if (preg_match('#^/(app|config|database|resources|routes|storage)(/|$)#', $path)
    || preg_match('#(^|/)\.#', $path)
    || preg_match('#(^|/)(installed\.lock|composer\.(json|lock))$#i', $path)
    || preg_match('#\.(ini|log|sql|sh|bak|md)$#i', $path)) {
    http_response_code(403);
    exit('Forbidden');
}

// Let the server deliver real files (assets, uploads) untouched.
$file = __DIR__ . $path;
if ($path !== '/' && is_file($file)) {
    return false;
}

require __DIR__ . '/index.php';
