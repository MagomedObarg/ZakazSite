<?php

namespace Tests\Support;

use App\Contracts\AiClient;
use App\Contracts\AnalysisResultStore;
use App\Contracts\ProcurementFetcher;
use App\Data\AnalysisResult;

class FakeProcurementFetcher implements ProcurementFetcher
{
    public int $calls = 0;

    public function __construct(private array $payload)
    {
    }

    public function fetch(string $url): array
    {
        $this->calls++;

        return $this->payload;
    }
}

class FakeAiClient implements AiClient
{
    public int $calls = 0;

    public function __construct(private array $response)
    {
    }

    public function analyze(array $payload): array
    {
        $this->calls++;

        return $this->response;
    }
}

class FakeAnalysisResultStore implements AnalysisResultStore
{
    /**
     * @var array<string, AnalysisResult>
     */
    private array $storage = [];

    public function save(AnalysisResult $result): void
    {
        $this->storage[$result->id] = $result;
    }

    public function find(string $id): ?AnalysisResult
    {
        return $this->storage[$id] ?? null;
    }

    public function clear(): void
    {
        $this->storage = [];
    }
}
