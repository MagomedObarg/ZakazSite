<?php

namespace App\Contracts;

interface AiClient
{
    /**
     * Sends the prepared procurement context to the AI model and returns the structured analysis.
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function analyze(array $payload): array;
}
