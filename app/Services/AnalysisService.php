<?php

namespace App\Services;

use App\Contracts\AiClient;
use App\Contracts\AnalysisResultStore;
use App\Contracts\ProcurementFetcher;
use App\Data\AnalysisResult;
use App\Exceptions\CachedErrorException;
use RuntimeException;
use Throwable;

class AnalysisService
{
    public function __construct(
        protected ProcurementFetcher $procurementFetcher,
        protected AiClient $aiClient,
        protected CacheService $cache,
        protected AnalysisResultStore $results
    ) {
    }

    public function analyzeUrl(string $url): AnalysisResult
    {
        $url = trim($url);

        if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new RuntimeException('A valid procurement URL is required.');
        }

        $cacheKey = $this->cacheKey($url);

        if ($this->cache->has($cacheKey)) {
            $cached = $this->cache->get($cacheKey);

            if (is_array($cached)) {
                if (isset($cached['is_error']) && $cached['is_error'] === true) {
                    throw new CachedErrorException($cached);
                }

                return AnalysisResult::fromArray($cached)->flagAsCached();
            }
        }

        try {
            $procurement = $this->procurementFetcher->fetch($url);

            $prompt = $this->buildPrompt($procurement);

            $aiResponse = $this->aiClient->analyze([
                'prompt' => $prompt,
                'procurement' => $procurement,
            ]);

            $analysis = [
                'summary' => $aiResponse['summary'] ?? '',
                'risks' => $aiResponse['risks'] ?? [],
                'recommendations' => $aiResponse['recommendations'] ?? [],
                'score' => $aiResponse['score'] ?? null,
            ];

            $meta = array_merge($aiResponse['meta'] ?? [], [
                'prompt' => $prompt,
            ]);

            $result = AnalysisResult::create(
                $this->identifier($url, $procurement),
                $url,
                $procurement,
                $analysis,
                $meta,
                false
            );

            $this->cache->put($cacheKey, $result->toArray(), 3600);
            $this->results->save($result);

            return $result;
        } catch (Throwable $exception) {
            $this->cacheError($cacheKey, $exception);
            throw $exception;
        }
    }

    protected function cacheError(string $cacheKey, Throwable $exception): void
    {
        $statusCode = 500;
        $errorCode = 'INTERNAL_ERROR';
        $userMessage = 'An unexpected error occurred while processing your request.';

        if (method_exists($exception, 'getStatusCode')) {
            $statusCode = $exception->getStatusCode();
        }

        if (method_exists($exception, 'getErrorCode')) {
            $errorCode = $exception->getErrorCode();
        } else {
            $className = get_class($exception);
            if (str_contains($className, 'ProcurementNotFoundException')) {
                $errorCode = 'PROCUREMENT_NOT_FOUND';
            } elseif (str_contains($className, 'ProcurementParseException')) {
                $errorCode = 'PROCUREMENT_PARSE_ERROR';
            } elseif (str_contains($className, 'DeepSeekException')) {
                $errorCode = 'AI_SERVICE_ERROR';
            }
        }

        if (method_exists($exception, 'getUserMessage')) {
            $userMessage = $exception->getUserMessage();
        }

        $errorData = [
            'is_error' => true,
            'status_code' => $statusCode,
            'error_code' => $errorCode,
            'user_message' => $userMessage,
            'exception_message' => $exception->getMessage(),
            'cached_at' => date('Y-m-d H:i:s'),
        ];

        $this->cache->put($cacheKey, $errorData, 3600);
    }

    public function find(string $id): ?AnalysisResult
    {
        return $this->results->find($id);
    }

    public function flush(): void
    {
        $this->cache->clear();
        $this->results->clear();
    }

    protected function buildPrompt(array $procurement): string
    {
        $title = $procurement['title'] ?? 'Procurement Opportunity';
        $buyer = $procurement['buyer']['name'] ?? 'Unknown buyer';
        $value = $procurement['value']['amount'] ?? 'n/a';
        $currency = $procurement['value']['currency'] ?? '';
        $deadline = $procurement['deadline'] ?? 'unspecified deadline';

        $items = array_map(function (array $item): string {
            return sprintf(
                '%s (%s %s)',
                $item['name'] ?? 'Item',
                $item['quantity'] ?? 'n/a',
                $item['unit'] ?? ''
            );
        }, $procurement['items'] ?? []);

        $itemsList = $items === [] ? 'No individual line items were provided.' : 'Items: ' . implode(', ', $items);

        return sprintf(
            'Analyze the procurement "%s" issued by %s valued at %s %s with a response deadline of %s. %s',
            $title,
            $buyer,
            $value,
            $currency,
            $deadline,
            $itemsList
        );
    }

    protected function cacheKey(string $url): string
    {
        return 'analysis:' . sha1($url);
    }

    protected function identifier(string $url, array $procurement): string
    {
        return sha1(json_encode([
            'url' => $url,
            'title' => $procurement['title'] ?? null,
            'deadline' => $procurement['deadline'] ?? null,
        ]));
    }
}
