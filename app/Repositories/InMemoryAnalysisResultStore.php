<?php

namespace App\Repositories;

use App\Contracts\AnalysisResultStore;
use App\Data\AnalysisResult;

class InMemoryAnalysisResultStore implements AnalysisResultStore
{
    /**
     * @var array<string, AnalysisResult>
     */
    protected array $storage = [];

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
