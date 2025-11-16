<?php

use App\Contracts\AiClient;
use App\Contracts\AnalysisResultStore;
use App\Contracts\ProcurementFetcher;
use App\Data\AnalysisResult;
use App\Exceptions\CachedErrorException;
use App\Exceptions\DeepSeekException;
use App\Exceptions\ProcurementNotFoundException;
use App\Services\AnalysisService;
use App\Services\CacheService;

it('caches error states for 1 hour when procurement fetch fails', function () {
    $fetcher = new class implements ProcurementFetcher {
        public int $calls = 0;

        public function fetch(string $url): array
        {
            $this->calls++;
            throw new ProcurementNotFoundException('Procurement not found');
        }
    };

    $aiClient = new class implements AiClient {
        public function analyze(array $payload): array
        {
            return [];
        }
    };

    $store = new class implements AnalysisResultStore {
        public function save(AnalysisResult $result): void {}
        public function find(string $id): ?AnalysisResult { return null; }
        public function clear(): void {}
    };

    $service = new AnalysisService($fetcher, $aiClient, new CacheService(), $store);

    try {
        $service->analyzeUrl('https://procure.test/missing');
    } catch (ProcurementNotFoundException $e) {
    }

    expect($fetcher->calls)->toBe(1);

    try {
        $service->analyzeUrl('https://procure.test/missing');
    } catch (CachedErrorException $e) {
        expect($e->getStatusCode())->toBe(404)
            ->and($e->getErrorData()['error_code'])->toBe('PROCUREMENT_NOT_FOUND');
    }

    expect($fetcher->calls)->toBe(1);
});

it('caches error states when AI service fails', function () {
    $fetcher = new class implements ProcurementFetcher {
        public function fetch(string $url): array
        {
            return ['title' => 'Test'];
        }
    };

    $aiClient = new class implements AiClient {
        public int $calls = 0;

        public function analyze(array $payload): array
        {
            $this->calls++;
            throw new DeepSeekException('AI service unavailable');
        }
    };

    $store = new class implements AnalysisResultStore {
        public function save(AnalysisResult $result): void {}
        public function find(string $id): ?AnalysisResult { return null; }
        public function clear(): void {}
    };

    $service = new AnalysisService($fetcher, $aiClient, new CacheService(), $store);

    try {
        $service->analyzeUrl('https://procure.test/test');
    } catch (DeepSeekException $e) {
    }

    expect($aiClient->calls)->toBe(1);

    try {
        $service->analyzeUrl('https://procure.test/test');
    } catch (CachedErrorException $e) {
        expect($e->getStatusCode())->toBe(502)
            ->and($e->getErrorData()['error_code'])->toBe('AI_SERVICE_ERROR');
    }

    expect($aiClient->calls)->toBe(1);
});

it('cached error includes timestamp and user message', function () {
    $fetcher = new class implements ProcurementFetcher {
        public function fetch(string $url): array
        {
            throw new ProcurementNotFoundException('Not found');
        }
    };

    $aiClient = new class implements AiClient {
        public function analyze(array $payload): array
        {
            return [];
        }
    };

    $store = new class implements AnalysisResultStore {
        public function save(AnalysisResult $result): void {}
        public function find(string $id): ?AnalysisResult { return null; }
        public function clear(): void {}
    };

    $service = new AnalysisService($fetcher, $aiClient, new CacheService(), $store);

    try {
        $service->analyzeUrl('https://procure.test/test');
    } catch (ProcurementNotFoundException $e) {
    }

    try {
        $service->analyzeUrl('https://procure.test/test');
    } catch (CachedErrorException $e) {
        $data = $e->getErrorData();
        expect($data['cached_at'])->toMatch('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/')
            ->and($data['user_message'])->toContain('could not be found')
            ->and($data['is_error'])->toBeTrue();
    }
});
