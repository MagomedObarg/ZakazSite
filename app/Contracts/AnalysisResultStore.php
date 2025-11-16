<?php

namespace App\Contracts;

use App\Data\AnalysisResult;

interface AnalysisResultStore
{
    public function save(AnalysisResult $result): void;

    public function find(string $id): ?AnalysisResult;

    public function clear(): void;
}
