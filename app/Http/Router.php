<?php

namespace App\Http;

use Closure;
use RuntimeException;

class Router
{
    /**
     * @var array<int, array{method:string, pattern:string, parameters:array<int, string>, handler:callable}>
     */
    protected array $routes = [];

    public function get(string $uri, callable $handler): void
    {
        $this->addRoute('GET', $uri, $handler);
    }

    public function post(string $uri, callable $handler): void
    {
        $this->addRoute('POST', $uri, $handler);
    }

    public function addRoute(string $method, string $uri, callable $handler): void
    {
        [$pattern, $parameters] = $this->compileRoute($uri);

        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $pattern,
            'parameters' => $parameters,
            'handler' => $handler,
        ];
    }

    public function dispatch(Request $request): Response
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method()) {
                continue;
            }

            if (preg_match($route['pattern'], $request->path(), $matches)) {
                $parameters = [];

                foreach ($route['parameters'] as $name) {
                    $parameters[] = $matches[$name] ?? null;
                }

                $handler = $route['handler'];

                if ($handler instanceof Closure) {
                    $response = $handler($request, ...$parameters);
                } else {
                    $response = $handler($request, ...$parameters);
                }

                if (! $response instanceof Response) {
                    throw new RuntimeException('Route handlers must return an instance of ' . Response::class);
                }

                return $response;
            }
        }

        return Response::view('errors.404', ['message' => 'Not Found'], 404);
    }

    /**
     * @return array{0:string,1:array<int, string>}
     */
    protected function compileRoute(string $uri): array
    {
        $parameterPattern = '/\{([a-zA-Z_][a-zA-Z0-9_-]*)\}/';
        $parameters = [];

        $pattern = preg_replace_callback($parameterPattern, function (array $matches) use (&$parameters) {
            $parameters[] = $matches[1];

            return '(?P<' . $matches[1] . '>[^/]+)';
        }, $uri);

        $regex = '#^' . $pattern . '$#';

        return [$regex, $parameters];
    }
}
