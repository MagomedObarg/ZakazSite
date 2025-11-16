<?php

namespace App\Support;

use Closure;
use InvalidArgumentException;
use RuntimeException;

class Http
{
    /**
     * @var (callable|array|null)
     */
    protected static $fake = null;

    /**
     * @var array<int, array<string, mixed>>
     */
    protected static array $history = [];

    /**
     * Register fake handlers used during testing.
     *
     * @param callable|array $fake
     */
    public static function fake(callable|array $fake): void
    {
        self::$fake = $fake;
        self::$history = [];
    }

    public static function reset(): void
    {
        self::$fake = null;
        self::$history = [];
    }

    public static function history(): array
    {
        return self::$history;
    }

    public static function response(string $body = '', int $status = 200, array $headers = []): HttpResponse
    {
        return new HttpResponse($status, $headers, $body);
    }

    public static function json(array $data, int $status = 200, array $headers = []): HttpResponse
    {
        $headers = array_merge(['Content-Type' => 'application/json'], $headers);

        return new HttpResponse($status, $headers, json_encode($data, JSON_PRETTY_PRINT));
    }

    public static function get(string $url, array $options = []): HttpResponse
    {
        return self::request('GET', $url, $options);
    }

    public static function post(string $url, array $options = []): HttpResponse
    {
        return self::request('POST', $url, $options);
    }

    protected static function request(string $method, string $url, array $options = []): HttpResponse
    {
        $payload = [
            'method' => strtoupper($method),
            'url' => $url,
            'options' => $options,
        ];

        self::$history[] = $payload;

        if (self::$fake !== null) {
            $fake = self::$fake;

            if ($fake instanceof Closure) {
                $response = $fake($payload);
            } elseif (is_array($fake)) {
                $response = self::resolveArrayFake($fake, $url, $payload);
            } else {
                throw new InvalidArgumentException('Invalid HTTP fake provided.');
            }

            if (! $response instanceof HttpResponse) {
                throw new RuntimeException('HTTP fake handlers must return an instance of ' . HttpResponse::class);
            }

            return $response;
        }

        throw new RuntimeException('Real HTTP requests are disabled in the testing environment.');
    }

    protected static function resolveArrayFake(array $fakes, string $url, array $payload): HttpResponse
    {
        foreach ($fakes as $pattern => $handler) {
            if ($pattern === '*' || preg_match(self::convertPatternToRegex($pattern), $url)) {
                if ($handler instanceof Closure) {
                    $response = $handler($payload);
                } elseif ($handler instanceof HttpResponse) {
                    $response = $handler;
                } elseif (is_array($handler)) {
                    $response = self::json($handler);
                } else {
                    throw new InvalidArgumentException('Invalid HTTP fake handler provided.');
                }

                return $response;
            }
        }

        throw new RuntimeException("No fake response registered for [{$url}]");
    }

    protected static function convertPatternToRegex(string $pattern): string
    {
        if (str_starts_with($pattern, '#') && str_ends_with($pattern, '#')) {
            return $pattern;
        }

        $quoted = preg_quote($pattern, '#');
        $quoted = str_replace('\*', '.*', $quoted);

        return '#^' . $quoted . '$#';
    }
}
