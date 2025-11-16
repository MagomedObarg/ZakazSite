<?php

namespace App\Services;

use App\Contracts\AiClient;
use App\Support\Http;
use RuntimeException;

class DeepSeekService implements AiClient
{
    public function __construct(
        protected string $endpoint,
        protected string $apiKey,
        protected string $model = 'deepseek-chat'
    ) {
    }

    public static function fromEnvironment(): self
    {
        $endpoint = getenv('DEEPSEEK_ENDPOINT') ?: 'https://api.deepseek.com/v1/analyze';
        $apiKey = getenv('DEEPSEEK_API_KEY') ?: '';
        $model = getenv('DEEPSEEK_MODEL') ?: 'deepseek-chat';

        return new self($endpoint, $apiKey, $model);
    }

    public function analyze(array $payload): array
    {
        $requestBody = [
            'model' => $this->model,
            'input' => $payload,
        ];

        $response = Http::post($this->endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => $requestBody,
        ]);

        if ($response->failed()) {
            throw new RuntimeException('DeepSeek API responded with failure status: ' . $response->status());
        }

        $body = $response->json();

        return [
            'summary' => $body['summary'] ?? '',
            'risks' => $body['risks'] ?? [],
            'recommendations' => $body['recommendations'] ?? [],
            'score' => isset($body['score']) ? (float) $body['score'] : null,
            'meta' => [
                'endpoint' => $this->endpoint,
                'model' => $body['model'] ?? $this->model,
                'usage' => $body['usage'] ?? [],
            ],
        ];
    }
}
