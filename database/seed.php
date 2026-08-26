<?php
declare(strict_types=1);

/**
 * CLI content importer.
 *
 *   php database/seed.php
 *
 * Re-runnable: entities are keyed by slug, so this refreshes imported content
 * without creating duplicates. Operator-edited settings are never overwritten.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This script may only be run from the command line.\n");
}

$basePath = dirname(__DIR__);

require $basePath . '/app/Core/Autoloader.php';
App\Core\Autoloader::register($basePath);
require $basePath . '/app/Support/helpers.php';

App\Core\Env::load($basePath . '/.env');
App\Core\Config::loadDirectory($basePath . '/config');
App\Core\Config::set('app.base_path', $basePath);
App\Core\Lang::set(App\Core\Lang::default());

try {
    $db     = App\Core\Database::instance();
    $seeder = new Database\Seeder($db, $basePath);
    $report = $seeder->run();

    echo "Content import complete.\n";
    foreach ($report as $key => $count) {
        printf("  %-18s %d\n", $key, $count);
    }
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, 'Import failed: ' . $e->getMessage() . "\n");
    fwrite(STDERR, $e->getFile() . ':' . $e->getLine() . "\n");
    exit(1);
}
