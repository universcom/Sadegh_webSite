<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\Setting;
use Throwable;

/**
 * Application bootstrap: environment, error handling, session, locale, routing.
 */
final class App
{
    private string $basePath;
    private Request $request;
    private Router $router;

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/');
    }

    public function boot(): self
    {
        Env::load($this->basePath . '/.env');
        Config::loadDirectory($this->basePath . '/config');
        Config::set('app.base_path', $this->basePath);

        date_default_timezone_set((string) Config::get('app.timezone', 'UTC'));
        mb_internal_encoding('UTF-8');

        $this->configureErrorHandling();

        Session::start();

        $this->request = new Request();

        return $this;
    }

    public function isInstalled(): bool
    {
        return is_file($this->basePath . '/installed.lock')
            && is_file($this->basePath . '/.env')
            && (string) Config::get('database.name', '') !== '';
    }

    private function configureErrorHandling(): void
    {
        $debug = Config::get('app.debug', false) && Config::get('app.env') !== 'production';

        ini_set('display_errors', $debug ? '1' : '0');
        ini_set('display_startup_errors', $debug ? '1' : '0');
        ini_set('log_errors', '1');
        error_reporting($debug ? E_ALL : E_ALL & ~E_DEPRECATED & ~E_STRICT);

        set_exception_handler(function (Throwable $e) use ($debug): void {
            Logger::error('Unhandled exception', $e);
            $this->renderError(500, $e, $debug);
        });

        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }

            // Deprecations and notices are recorded, never thrown: a new PHP
            // release deprecating a function must not take the site down.
            if (in_array($severity, [E_DEPRECATED, E_USER_DEPRECATED, E_NOTICE, E_USER_NOTICE, E_WARNING, E_USER_WARNING], true)) {
                Logger::warning($message, ['file' => basename($file), 'line' => $line]);

                return true;
            }

            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        register_shutdown_function(function () use ($debug): void {
            $error = error_get_last();
            if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                Logger::error('Fatal error: ' . $error['message'], null, ['file' => basename($error['file']), 'line' => $error['line']]);
                if (!headers_sent()) {
                    $this->renderError(500, null, $debug);
                }
            }
        });
    }

    private function renderError(int $status, ?Throwable $e, bool $debug): void
    {
        if (headers_sent()) {
            exit;
        }

        http_response_code($status);
        header('Content-Type: text/html; charset=UTF-8');

        if ($debug && $e !== null) {
            echo '<!doctype html><meta charset="utf-8"><title>Application error</title>';
            echo '<pre style="font:13px/1.6 ui-monospace,monospace;padding:24px;white-space:pre-wrap">';
            echo htmlspecialchars(
                $e::class . ': ' . $e->getMessage() . "\n\n"
                . $e->getFile() . ':' . $e->getLine() . "\n\n"
                . $e->getTraceAsString(),
                ENT_QUOTES,
                'UTF-8'
            );
            echo '</pre>';
            exit;
        }

        // Production: a designed page, with no internal detail whatsoever.
        try {
            echo View::renderWithLayout('site.error', 'layouts.site', [
                'status'      => $status,
                'metaTitle'   => Lang::get('error.500.title'),
                'metaRobots'  => 'noindex, nofollow',
                'errorTitle'  => Lang::get('error.500.title'),
                'errorBody'   => Lang::get('error.500.body'),
            ]);
        } catch (Throwable) {
            echo '<!doctype html><meta charset="utf-8"><title>Server error</title>'
               . '<p style="font-family:system-ui;padding:40px">Something went wrong. Please try again later.</p>';
        }

        exit;
    }

    public function request(): Request
    {
        return $this->request;
    }

    public function router(): Router
    {
        return $this->router;
    }

    /**
     * Resolve the active locale, share global view state and dispatch.
     */
    public function run(): void
    {
        $this->resolveLocale();

        Setting::preload();

        View::share('request', $this->request);
        View::share('locale', Lang::current());
        View::share('direction', Lang::direction());
        View::share('locales', Lang::enabled());
        View::share('settings', Setting::all());

        $this->router = new Router();
        (require $this->basePath . '/routes/web.php')($this->router);

        $this->router->dispatch($this->request);
    }

    /**
     * The first path segment decides the language. A request without one is
     * redirected to the visitor's best match so every public URL is canonical
     * and cacheable.
     */
    private function resolveLocale(): void
    {
        $segments = $this->request->segments();
        $first    = $segments[0] ?? '';

        if (Lang::isSupported($first)) {
            Lang::set($first);
            Session::put('_locale', $first);

            return;
        }

        // Admin, sitemap and asset routes run in the operator's chosen language.
        if (in_array($first, ['admin', 'sitemap.xml', 'robots.txt', 'install.php'], true)) {
            Lang::set((string) Session::get('_locale', Lang::default()));

            return;
        }

        Lang::set(Lang::default());

        // Only redirect real page requests, never missing assets.
        if (!preg_match('/\.[A-Za-z0-9]{2,5}$/', $this->request->path())) {
            $path  = trim($this->request->path(), '/');
            $query = $_GET === [] ? '' : '?' . http_build_query($_GET);

            // An un-prefixed URL always resolves to the configured default
            // language. The audience for this site is in Iran, so Persian must
            // win regardless of what Accept-Language the browser happens to
            // send — a visitor wanting another language uses the switcher,
            // which produces an explicit /en/ or /ar/ URL.
            Response::redirect(Url::lang($path, Lang::default()) . $query, 302);
        }
    }
}
