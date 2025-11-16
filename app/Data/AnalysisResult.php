<?php

namespace App\Data;

use Carbon\CarbonImmutable;

class AnalysisResult
{
    public function __construct(
        public readonly string $id,
        public readonly string $url,
        public readonly array $procurement,
        public readonly array $analysis,
        public readonly array $meta,
        public readonly bool $fromCache = false,
        public readonly string $generatedAt = ''
    ) {
    }

    public static function create(
        string $id,
        string $url,
        array $procurement,
        array $analysis,
        array $meta = [],
        bool $fromCache = false,
        ?string $generatedAt = null
    ): self {
        return new self(
            $id,
            $url,
            $procurement,
            $analysis,
            $meta,
            $fromCache,
            $generatedAt ?? CarbonImmutable::now()->toIso8601String()
        );
    }

    public function flagAsCached(): self
    {
        return new self(
            $this->id,
            $this->url,
            $this->procurement,
            $this->analysis,
            $this->meta,
            true,
            $this->generatedAt
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'procurement' => $this->procurement,
            'analysis' => $this->analysis,
            'meta' => $this->meta,
            'from_cache' => $this->fromCache,
            'generated_at' => $this->generatedAt,
        ];
    }

    public static function fromArray(array $payload): self
    {
        return new self(
            $payload['id'],
            $payload['url'],
            $payload['procurement'],
            $payload['analysis'],
            $payload['meta'] ?? [],
            $payload['from_cache'] ?? false,
            $payload['generated_at'] ?? CarbonImmutable::now()->toIso8601String()
        );
    }
}
