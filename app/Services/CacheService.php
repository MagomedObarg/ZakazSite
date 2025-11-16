<?php

namespace App\Services;

use Closure;

class CacheService
{
    /**
     * @var array<string, array{value:mixed, expires_at:float|null}>
     */
    protected array $items = [];

    /**
     * @var callable
     */
    protected $clock;

    public function __construct(?callable $clock = null)
    {
        $this->clock = $clock ?? static fn (): float => microtime(true);
    }

    public function put(string $key, mixed $value, ?int $seconds = null): void
    {
        $expiresAt = $seconds !== null ? $this->now() + $seconds : null;
        $this->items[$key] = [
            'value' => $value,
            'expires_at' => $expiresAt,
        ];
    }

    public function remember(string $key, int $seconds, Closure $callback): mixed
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        $value = $callback();
        $this->put($key, $value, $seconds);

        return $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (! array_key_exists($key, $this->items)) {
            return $default;
        }

        $item = $this->items[$key];

        if ($item['expires_at'] !== null && $item['expires_at'] <= $this->now()) {
            unset($this->items[$key]);

            return $default;
        }

        return $item['value'];
    }

    public function has(string $key): bool
    {
        if (! array_key_exists($key, $this->items)) {
            return false;
        }

        $item = $this->items[$key];

        if ($item['expires_at'] !== null && $item['expires_at'] <= $this->now()) {
            unset($this->items[$key]);

            return false;
        }

        return true;
    }

    public function forget(string $key): void
    {
        unset($this->items[$key]);
    }

    public function clear(): void
    {
        $this->items = [];
    }

    protected function now(): float
    {
        $clock = $this->clock;

        return (float) $clock();
    }
}
