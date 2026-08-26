<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Pattern router. Routes are registered as "/products/{slug}" and matched
 * against the request path; {param} captures one segment.
 */
final class Router
{
    /** @var array<string,array<int,array{regex:string,params:array<int,string>,handler:callable|array,middleware:array}>> */
    private array $routes = [];
    /** @var callable|null */
    private $fallback = null;

    public function get(string $pattern, callable|array $handler, array $middleware = []): void
    {
        $this->add('GET', $pattern, $handler, $middleware);
    }

    public function post(string $pattern, callable|array $handler, array $middleware = []): void
    {
        $this->add('POST', $pattern, $handler, $middleware);
    }

    /** Register the same handler for GET and POST. */
    public function form(string $pattern, callable|array $handler, array $middleware = []): void
    {
        $this->add('GET', $pattern, $handler, $middleware);
        $this->add('POST', $pattern, $handler, $middleware);
    }

    public function any(array $methods, string $pattern, callable|array $handler, array $middleware = []): void
    {
        foreach ($methods as $method) {
            $this->add(strtoupper($method), $pattern, $handler, $middleware);
        }
    }

    public function fallback(callable $handler): void
    {
        $this->fallback = $handler;
    }

    private function add(string $method, string $pattern, callable|array $handler, array $middleware): void
    {
        $params = [];

        $regex = preg_replace_callback(
            '#\{([A-Za-z_][A-Za-z0-9_]*)(?::([^}]+))?\}#',
            static function (array $m) use (&$params): string {
                $params[] = $m[1];

                return '(' . ($m[2] ?? '[^/]+') . ')';
            },
            $pattern
        );

        $this->routes[$method][] = [
            'regex'      => '#^' . $regex . '$#u',
            'params'     => $params,
            'handler'    => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $path   = $request->path();

        // HEAD is served by the GET handler with the body discarded.
        $lookup = $method === 'HEAD' ? 'GET' : $method;

        foreach ($this->routes[$lookup] ?? [] as $route) {
            if (!preg_match($route['regex'], $path, $matches)) {
                continue;
            }

            array_shift($matches);
            $params = array_combine($route['params'], $matches) ?: [];

            foreach ($route['middleware'] as $middleware) {
                $middleware($request, $params);
            }

            $this->invoke($route['handler'], $request, $params);

            return;
        }

        // Path exists under another verb — report that rather than 404.
        foreach ($this->routes as $verb => $routes) {
            if ($verb === $lookup) {
                continue;
            }
            foreach ($routes as $route) {
                if (preg_match($route['regex'], $path)) {
                    if (!headers_sent()) {
                        header('Allow: ' . $verb);
                    }
                    Response::text('Method Not Allowed', 405);
                }
            }
        }

        if ($this->fallback !== null) {
            ($this->fallback)($request);

            return;
        }

        Response::text('Not Found', 404);
    }

    private function invoke(callable|array $handler, Request $request, array $params): void
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            $controller       = new $class();
            $controller->$method($request, $params);

            return;
        }

        $handler($request, $params);
    }
}
