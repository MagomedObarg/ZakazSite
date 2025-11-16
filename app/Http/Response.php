<?php

namespace App\Http;

class Response
{
    protected ?string $view = null;

    protected array $data = [];

    protected ?string $redirectTo = null;

    protected ?array $json = null;

    protected array $flash = [];

    public function __construct(
        protected int $status = 200,
        protected array $headers = [],
        protected ?string $content = null,
    ) {
    }

    public static function view(string $view, array $data = [], int $status = 200, array $headers = []): self
    {
        $response = new self($status, $headers);
        $response->view = $view;
        $response->data = $data;

        return $response;
    }

    public static function redirect(string $to, array $flash = [], int $status = 302, array $headers = []): self
    {
        $headers = array_merge($headers, ['Location' => $to]);
        $response = new self($status, $headers);
        $response->redirectTo = $to;
        $response->flash = $flash;

        return $response;
    }

    public static function json(array $payload, int $status = 200, array $headers = []): self
    {
        $headers = array_merge(['Content-Type' => 'application/json'], $headers);
        $response = new self($status, $headers, json_encode($payload, JSON_PRETTY_PRINT));
        $response->json = $payload;

        return $response;
    }

    public static function text(string $content, int $status = 200, array $headers = []): self
    {
        return new self($status, $headers, $content);
    }

    public function isRedirect(): bool
    {
        return $this->redirectTo !== null;
    }

    public function isView(): bool
    {
        return $this->view !== null;
    }

    public function isJson(): bool
    {
        return $this->json !== null;
    }

    public function redirectTo(): ?string
    {
        return $this->redirectTo;
    }

    public function view(): ?string
    {
        return $this->view;
    }

    public function data(): array
    {
        return $this->data;
    }

    public function jsonData(): ?array
    {
        return $this->json;
    }

    public function status(): int
    {
        return $this->status;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function flash(): array
    {
        return $this->flash;
    }

    public function withFlash(string $key, mixed $value): self
    {
        $this->flash[$key] = $value;

        return $this;
    }

    public function content(): ?string
    {
        return $this->content;
    }
}
