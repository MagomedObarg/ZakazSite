<?php

use App\Services\DeepSeekService;
use App\Support\Http;
use RuntimeException;

it('constructs DeepSeek requests with auth headers and parses the response', function () {
    $captured = [];

    Http::fake(function (array $payload) use (&$captured) {
        $captured = $payload;

        return Http::json([
            'summary' => 'Vendors should highlight experience refurbishing urban parks.',
            'risks' => ['Tight delivery timeline', 'Budget pressure due to inflation'],
            'recommendations' => ['Provide phased delivery plan', 'Outline maintenance guarantees'],
            'score' => 0.87,
            'model' => 'deepseek-chat',
            'usage' => ['prompt_tokens' => 120, 'completion_tokens' => 240],
        ]);
    });

    $service = new DeepSeekService('https://api.deepseek.test/v1/analyze', 'test-api-key', 'deepseek-chat');

    $result = $service->analyze([
        'prompt' => 'Analyze procurement context',
        'procurement' => ['title' => 'Central Park Renewal Works'],
    ]);

    expect($captured['url'])->toBe('https://api.deepseek.test/v1/analyze')
        ->and($captured['method'])->toBe('POST')
        ->and($captured['options']['headers']['Authorization'])->toBe('Bearer test-api-key')
        ->and($captured['options']['json']['model'])->toBe('deepseek-chat')
        ->and($captured['options']['json']['input']['prompt'])->toBe('Analyze procurement context')
        ->and($result['summary'])->toContain('Vendors should highlight')
        ->and($result['score'])->toBe(0.87)
        ->and($result['meta']['usage']['prompt_tokens'])->toBe(120);
});

it('throws when DeepSeek responds with an error status', function () {
    Http::fake([
        '*' => Http::response('Internal error', 500),
    ]);

    $service = new DeepSeekService('https://api.deepseek.test/v1/analyze', 'test-api-key');

    expect(fn () => $service->analyze(['prompt' => 'x', 'procurement' => []]))
        ->toThrow(RuntimeException::class, 'DeepSeek API responded with failure status: 500');
});
