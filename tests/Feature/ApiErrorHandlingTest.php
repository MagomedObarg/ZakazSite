<?php

use App\Application;
use App\Contracts\AiClient;
use App\Contracts\ProcurementFetcher;
use App\Exceptions\DeepSeekException;
use App\Exceptions\ProcurementNotFoundException;
use App\Exceptions\ProcurementParseException;
use App\Http\Request;
use App\Services\CacheService;
use Tests\Support\FakeAiClient;
use Tests\Support\FakeAnalysisResultStore;

it('returns 404 error code for procurement not found', function () {
    $app = Application::create([
        'cache' => new CacheService(),
        'procurementFetcher' => new class implements ProcurementFetcher {
            public function fetch(string $url): array
            {
                throw new ProcurementNotFoundException();
            }
        },
        'aiClient' => new FakeAiClient(['summary' => 'test']),
        'resultStore' => new FakeAnalysisResultStore(),
    ]);

    $response = $app->handle(Request::fromArray([
        'method' => 'POST',
        'path' => '/api/analyze',
        'json' => ['procurement_url' => 'https://procure.test/missing'],
    ]));

    expect($response->status())->toBe(404)
        ->and($response->jsonData()['status'])->toBe('error')
        ->and($response->jsonData()['error_code'])->toBe('PROCUREMENT_NOT_FOUND')
        ->and($response->jsonData()['message'])->toContain('could not be found');
});

it('returns 422 error code for parse errors', function () {
    $app = Application::create([
        'cache' => new CacheService(),
        'procurementFetcher' => new class implements ProcurementFetcher {
            public function fetch(string $url): array
            {
                throw new ProcurementParseException('Invalid HTML structure');
            }
        },
        'aiClient' => new FakeAiClient(['summary' => 'test']),
        'resultStore' => new FakeAnalysisResultStore(),
    ]);

    $response = $app->handle(Request::fromArray([
        'method' => 'POST',
        'path' => '/api/analyze',
        'json' => ['procurement_url' => 'https://procure.test/invalid'],
    ]));

    expect($response->status())->toBe(422)
        ->and($response->jsonData()['error_code'])->toBe('PROCUREMENT_PARSE_ERROR')
        ->and($response->jsonData()['message'])->toContain('invalid or incomplete');
});

it('returns 502 error code for AI service failures', function () {
    $app = Application::create([
        'cache' => new CacheService(),
        'procurementFetcher' => new class implements ProcurementFetcher {
            public function fetch(string $url): array
            {
                return ['title' => 'Test Procurement'];
            }
        },
        'aiClient' => new class implements AiClient {
            public function analyze(array $payload): array
            {
                throw new DeepSeekException('Service timeout');
            }
        },
        'resultStore' => new FakeAnalysisResultStore(),
    ]);

    $response = $app->handle(Request::fromArray([
        'method' => 'POST',
        'path' => '/api/analyze',
        'json' => ['procurement_url' => 'https://procure.test/test'],
    ]));

    expect($response->status())->toBe(502)
        ->and($response->jsonData()['error_code'])->toBe('AI_SERVICE_ERROR')
        ->and($response->jsonData()['message'])->toContain('temporarily unavailable');
});

it('serves cached errors without hitting external services', function () {
    $fetcherCallCount = 0;

    $app = Application::create([
        'cache' => new CacheService(),
        'procurementFetcher' => new class(&$fetcherCallCount) implements ProcurementFetcher {
            public function __construct(private int &$calls) {}

            public function fetch(string $url): array
            {
                $this->calls++;
                throw new ProcurementNotFoundException();
            }
        },
        'aiClient' => new FakeAiClient(['summary' => 'test']),
        'resultStore' => new FakeAnalysisResultStore(),
    ]);

    $firstResponse = $app->handle(Request::fromArray([
        'method' => 'POST',
        'path' => '/api/analyze',
        'json' => ['procurement_url' => 'https://procure.test/cached-error'],
    ]));

    expect($firstResponse->status())->toBe(404)
        ->and($fetcherCallCount)->toBe(1);

    $secondResponse = $app->handle(Request::fromArray([
        'method' => 'POST',
        'path' => '/api/analyze',
        'json' => ['procurement_url' => 'https://procure.test/cached-error'],
    ]));

    expect($secondResponse->status())->toBe(404)
        ->and($secondResponse->jsonData()['error_code'])->toBe('PROCUREMENT_NOT_FOUND')
        ->and($fetcherCallCount)->toBe(1);
});
