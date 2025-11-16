<?php

namespace App\Services;

use App\Contracts\AiClient;
use App\Contracts\AnalysisResultStore;
use App\Contracts\ProcurementFetcher;
use App\Data\AnalysisResult;
use RuntimeException;

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
                return AnalysisResult::fromArray($cached)->flagAsCached();
            }
        }

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
