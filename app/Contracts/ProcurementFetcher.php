<?php

namespace App\Contracts;

interface ProcurementFetcher
{
    /**
     * Fetches and parses procurement data from the provided URL.
     *
     * @return array<string, mixed>
     */
    public function fetch(string $url): array;
}
