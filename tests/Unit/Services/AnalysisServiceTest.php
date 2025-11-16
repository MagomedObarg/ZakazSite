<?php

use App\Contracts\AiClient;
use App\Contracts\AnalysisResultStore;
use App\Contracts\ProcurementFetcher;
use App\Data\AnalysisResult;
use App\Services\AnalysisService;
use App\Services\CacheService;
use RuntimeException;

it('runs the full analysis workflow, persists results, and caches subsequent lookups', function () {
    $fetcher = new class implements ProcurementFetcher {
        public int $calls = 0;

        public function fetch(string $url): array
        {
            $this->calls++;

            return [
                'title' => 'Central Park Renewal Works',
                'buyer' => ['name' => 'City of Springfield Procurement Office'],
                'deadline' => '2024-08-01',
                'value' => ['amount' => 245000, 'currency' => 'USD'],
                'items' => [
                    ['name' => 'Playground equipment replacement', 'quantity' => 5, 'unit' => 'lots'],
                ],
            ];
        }
    };

    $aiClient = new class implements AiClient {
        public int $calls = 0;

        public function analyze(array $payload): array
        {
            $this->calls++;

            return [
                'summary' => 'Vendors should highlight relevant park refurbishment projects.',
                'risks' => ['Weather delays'],
                'recommendations' => ['Include timeline buffers'],
                'score' => 0.91,
                'meta' => ['model' => 'deepseek-chat'],
            ];
        }
    };

    $store = new class implements AnalysisResultStore {
        /** @var array<string, AnalysisResult> */
        public array $items = [];

        public function save(AnalysisResult $result): void
        {
            $this->items[$result->id] = $result;
        }

        public function find(string $id): ?AnalysisResult
        {
            return $this->items[$id] ?? null;
        }

        public function clear(): void
        {
            $this->items = [];
        }
    };

    $service = new AnalysisService($fetcher, $aiClient, new CacheService(), $store);

    $result = $service->analyzeUrl('https://procure.test/tenders/42');

    expect($result->analysis['summary'])->toContain('Vendors should highlight')
        ->and($result->fromCache)->toBeFalse()
        ->and($aiClient->calls)->toBe(1)
        ->and($fetcher->calls)->toBe(1)
        ->and($store->find($result->id))->toBeInstanceOf(AnalysisResult::class);

    $cached = $service->analyzeUrl('https://procure.test/tenders/42');

    expect($cached->fromCache)->toBeTrue()
        ->and($aiClient->calls)->toBe(1)
        ->and($fetcher->calls)->toBe(1);
});

it('rejects invalid procurement URLs', function () {
    $service = new AnalysisService(
        new class implements ProcurementFetcher {
            public function fetch(string $url): array
            {
                return [];
            }
        },
        new class implements AiClient {
            public function analyze(array $payload): array
            {
                return [];
            }
        },
        new CacheService(),
        new class implements AnalysisResultStore {
            public function save(AnalysisResult $result): void {}
            public function find(string $id): ?AnalysisResult { return null; }
            public function clear(): void {}
        }
    );

    expect(fn () => $service->analyzeUrl('not-a-valid-url'))
        ->toThrow(RuntimeException::class, 'A valid procurement URL is required.');
});

it('flush clears both cache and result store', function () {
    $store = new class implements AnalysisResultStore {
        /** @var array<string, AnalysisResult> */
        public array $storage = [];

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
    };

    $service = new AnalysisService(
        new class implements ProcurementFetcher {
            public function fetch(string $url): array
            {
                return ['title' => 'Demo'];
            }
        },
        new class implements AiClient {
            public function analyze(array $payload): array
            {
                return ['summary' => 'Demo', 'risks' => [], 'recommendations' => [], 'score' => 0.5];
            }
        },
        new CacheService(),
        $store
    );

    $result = $service->analyzeUrl('https://example.com/demo');
    expect($store->find($result->id))->not()->toBeNull();

    $service->flush();

    expect($store->find($result->id))->toBeNull();
});
