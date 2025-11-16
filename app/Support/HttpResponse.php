<?php

namespace App\Support;

use RuntimeException;

class HttpResponse
{
    public function __construct(
        protected int $status,
        protected array $headers,
        protected string $body
    ) {
    }

    public function status(): int
    {
        return $this->status;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function json(): array
    {
        $decoded = json_decode($this->body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Failed to decode JSON response: ' . json_last_error_msg());
        }

        return $decoded;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function ok(): bool
    {
        return $this->status >= 200 && $this->status < 300;
    }

    public function failed(): bool
    {
        return ! $this->ok();
    }
}
