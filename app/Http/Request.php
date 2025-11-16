<?php

namespace App\Http;

use App\Support\SessionStore;

class Request
{
    public function __construct(
        protected string $method,
        protected string $path,
        protected array $query = [],
        protected array $input = [],
        protected array $json = [],
        protected ?SessionStore $session = null
    ) {
        $this->method = strtoupper($method);
    }

    public static function fromArray(array $payload, ?SessionStore $session = null): self
    {
        return new self(
            $payload['method'] ?? 'GET',
            $payload['path'] ?? '/',
            $payload['query'] ?? [],
            $payload['input'] ?? [],
            $payload['json'] ?? [],
            $session
        );
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->input[$key] ?? $this->json[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->input, $this->json);
    }

    public function json(): array
    {
        return $this->json;
    }

    public function session(): SessionStore
    {
        if ($this->session === null) {
            $this->session = new SessionStore();
        }

        return $this->session;
    }

    public function setSession(SessionStore $session): void
    {
        $this->session = $session;
    }
}
