<?php

namespace App\Support;

class SessionStore
{
    protected array $data = [];

    protected array $flash = [];

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function put(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data) || array_key_exists($key, $this->flash);
    }

    public function all(): array
    {
        return $this->data;
    }

    public function flash(string $key, mixed $value): void
    {
        $this->flash[$key] = $value;
    }

    public function flushFlash(): array
    {
        $flashed = $this->flash;
        $this->flash = [];

        return $flashed;
    }

    public function peekFlash(?string $key = null): mixed
    {
        if ($key === null) {
            return $this->flash;
        }

        return $this->flash[$key] ?? null;
    }

    public function reset(): void
    {
        $this->data = [];
        $this->flash = [];
    }
}
