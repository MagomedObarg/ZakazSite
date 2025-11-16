<?php

use App\Application;
use App\Http\Request;
use App\Services\CacheService;
use RuntimeException;
use Tests\Support\FakeAiClient;
use Tests\Support\FakeAnalysisResultStore;
use Tests\Support\FakeProcurementFetcher;

function make_api_application(): array
{
    $procurement = [
        'title' => 'Central Park Renewal Works',
        'buyer' => ['name' => 'City of Springfield Procurement Office'],
        'deadline' => '2024-08-01',
        'value' => ['amount' => 245000, 'currency' => 'USD'],
        'items' => [
            ['name' => 'Playground equipment replacement', 'quantity' => 5, 'unit' => 'lots'],
        ],
    ];

    $aiResponse = [
        'summary' => 'Analysis summary for API consumers.',
        'risks' => ['Supply chain volatility'],
        'recommendations' => ['Secure strategic suppliers early'],
        'score' => 0.88,
        'meta' => ['model' => 'deepseek-chat'],
    ];

    $fetcher = new FakeProcurementFetcher($procurement);
    $aiClient = new FakeAiClient($aiResponse);
    $store = new FakeAnalysisResultStore();

    $app = Application::create([
        'cache' => new CacheService(),
        'procurementFetcher' => $fetcher,
        'aiClient' => $aiClient,
        'resultStore' => $store,
    ]);

    return [$app, $aiClient, $fetcher, $store];
}

it('returns structured JSON responses for successful analyses', function () {
    [$app] = make_api_application();

    $response = $app->handle(Request::fromArray([
        'method' => 'POST',
        'path' => '/api/analyze',
        'json' => ['procurement_url' => 'https://procure.test/tender/55'],
    ]));

    expect($response->isJson())->toBeTrue()
        ->and($response->status())->toBe(200);

    $payload = $response->jsonData();

    expect($payload['status'])->toBe('ok')
        ->and($payload['data']['url'])->toBe('https://procure.test/tender/55')
        ->and($payload['data']['analysis']['score'])->toBe(0.88)
        ->and($payload['data']['procurement']['title'])->toBe('Central Park Renewal Works');
});

it('validates incoming JSON payloads', function () {
    [$app] = make_api_application();

    $response = $app->handle(Request::fromArray([
        'method' => 'POST',
        'path' => '/api/analyze',
        'json' => ['procurement_url' => 'invalid-url'],
    ]));

    expect($response->status())->toBe(422);
    $payload = $response->jsonData();

    expect($payload['errors']['procurement_url'][0])->toContain('valid URL');
});

it('returns cached analyses on subsequent API calls', function () {
    [$app, $aiClient] = make_api_application();

    $first = $app->handle(Request::fromArray([
        'method' => 'POST',
        'path' => '/api/analyze',
        'json' => ['procurement_url' => 'https://procure.test/tender/77'],
    ]));

    $second = $app->handle(Request::fromArray([
        'method' => 'POST',
        'path' => '/api/analyze',
        'json' => ['procurement_url' => 'https://procure.test/tender/77'],
    ]));

    $firstPayload = $first->jsonData();
    $secondPayload = $second->jsonData();

    expect($aiClient->calls)->toBe(1)
        ->and($secondPayload['data']['id'])->toBe($firstPayload['data']['id'])
        ->and($secondPayload['data']['from_cache'])->toBeTrue();
});

it('translates domain validation errors to JSON responses', function () {
    $app = Application::create([
        'cache' => new CacheService(),
        'procurementFetcher' => new class implements App\Contracts\ProcurementFetcher {
            public function fetch(string $url): array
            {
                throw new RuntimeException('Procurement document rejected');
            }
        },
        'aiClient' => new FakeAiClient([
            'summary' => 'should not run',
            'risks' => [],
            'recommendations' => [],
            'score' => 0,
        ]),
        'resultStore' => new FakeAnalysisResultStore(),
    ]);

    $response = $app->handle(Request::fromArray([
        'method' => 'POST',
        'path' => '/api/analyze',
        'json' => ['procurement_url' => 'https://procure.test/invalid'],
    ]));

    expect($response->status())->toBe(400);
    $payload = $response->jsonData();
    expect($payload['message'])->toContain('Procurement document rejected');
});

it('handles unexpected exceptions gracefully', function () {
    $app = Application::create([
        'cache' => new CacheService(),
        'procurementFetcher' => new FakeProcurementFetcher(['title' => 'Demo']),
        'aiClient' => new class implements App\Contracts\AiClient {
            public function analyze(array $payload): array
            {
                throw new Exception('Third-party outage');
            }
        },
        'resultStore' => new FakeAnalysisResultStore(),
    ]);

    $response = $app->handle(Request::fromArray([
        'method' => 'POST',
        'path' => '/api/analyze',
        'json' => ['procurement_url' => 'https://procure.test/boom'],
    ]));

    expect($response->status())->toBe(500);
    $payload = $response->jsonData();
    expect($payload['message'])->toContain('Unable to process');
});
