<?php

namespace App\Services;

use App\Contracts\ProcurementFetcher;
use App\Exceptions\ProcurementNotFoundException;
use App\Exceptions\ProcurementParseException;
use App\Support\Http;
use DOMDocument;
use DOMXPath;

class ProcurementParser implements ProcurementFetcher
{
    public function fetch(string $url): array
    {
        $response = Http::get($url);

        if ($response->status() === 404) {
            throw new ProcurementNotFoundException('Procurement notice not found at the specified URL.');
        }

        if ($response->failed()) {
            throw new ProcurementNotFoundException('Failed to download procurement notice.');
        }

        return $this->parse($response->body());
    }

    /**
     * @return array<string, mixed>
     */
    public function parse(string $html): array
    {
        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $loaded = $document->loadHTML($html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $loaded) {
            throw new ProcurementParseException('Invalid procurement document received.');
        }

        $xpath = new DOMXPath($document);
        $articleQuery = "//article[contains(@class, 'procurement')]";
        $article = $xpath->query($articleQuery)->item(0);

        if (! $article) {
            throw new ProcurementParseException('Unable to locate procurement container in the document.');
        }

        $title = trim($xpath->evaluate('string(.//h1)', $article));
        $buyerName = trim($xpath->evaluate('string(.//*[contains(@class, "buyer")])', $article));
        $buyerCountry = trim($xpath->evaluate('string(.//*[contains(@class, "buyer")]/@data-country)', $article));
        $deadline = trim($xpath->evaluate('string(.//time/@data-deadline)', $article));
        $description = trim($xpath->evaluate('string(.//*[contains(@class, "description")])', $article));

        $valueNode = $xpath->query('.//dl[contains(@class, "financial")]//dd', $article)->item(0);
        $valueAmount = $valueNode ? $this->toFloat($valueNode->textContent) : null;
        $valueCurrency = $valueNode?->attributes?->getNamedItem('data-currency')?->nodeValue;

        $items = [];
        foreach ($xpath->query('.//ul[contains(@class, "items")]//li', $article) as $itemNode) {
            $items[] = [
                'name' => trim($itemNode->textContent),
                'quantity' => $this->toFloat($itemNode->attributes?->getNamedItem('data-quantity')?->nodeValue ?? '0'),
                'unit' => trim($itemNode->attributes?->getNamedItem('data-unit')?->nodeValue ?? ''),
            ];
        }

        return [
            'title' => $title,
            'description' => $description,
            'buyer' => array_filter([
                'name' => $buyerName,
                'country' => $buyerCountry,
            ]),
            'deadline' => $deadline,
            'value' => array_filter([
                'amount' => $valueAmount,
                'currency' => $valueCurrency,
            ], static fn ($value) => $value !== null && $value !== ''),
            'items' => $items,
        ];
    }

    protected function toFloat(?string $value): ?float
    {
        if ($value === null) {
            return null;
        }

        $normalized = preg_replace('/[^0-9.,-]/', '', $value);
        $normalized = str_replace(',', '.', $normalized ?? '');

        if ($normalized === '' || $normalized === null) {
            return null;
        }

        return (float) $normalized;
    }
}
