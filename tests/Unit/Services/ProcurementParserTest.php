<?php

use App\Services\ProcurementParser;
use App\Support\Http;

beforeEach(function () {
    Http::reset();
});

it('parses procurement HTML into a structured payload', function () {
    $parser = new ProcurementParser();

    $result = $parser->parse(sample_procurement_html());

    expect($result['title'])->toBe('Central Park Renewal Works')
        ->and($result['buyer']['name'])->toBe('City of Springfield Procurement Office')
        ->and($result['buyer']['country'])->toBe('US')
        ->and($result['value']['amount'])->toBe(245000.0)
        ->and($result['value']['currency'])->toBe('USD')
        ->and($result['deadline'])->toBe('2024-08-01')
        ->and($result['items'])->toHaveCount(2)
        ->and($result['items'][0]['name'])->toBe('Playground equipment replacement');
});

it('fetches procurement HTML via the HTTP client abstraction', function () {
    Http::fake([
        'https://procure.test/tender/42' => Http::response(sample_procurement_html()),
    ]);

    $parser = new ProcurementParser();

    $result = $parser->fetch('https://procure.test/tender/42');

    expect(Http::history())->toHaveCount(1)
        ->and($result['title'])->toBe('Central Park Renewal Works');
});

function sample_procurement_html(): string
{
    return <<<HTML
    <article class="procurement">
        <h1>Central Park Renewal Works</h1>
        <div class="buyer" data-country="US">City of Springfield Procurement Office</div>
        <p class="description">The city is seeking suppliers for the refurbishment of the central park area.</p>
        <dl class="financial">
            <dt>Estimated value</dt>
            <dd data-currency="USD">245000</dd>
        </dl>
        <time data-deadline="2024-08-01">August 01, 2024</time>
        <ul class="items">
            <li data-quantity="5" data-unit="lots">Playground equipment replacement</li>
            <li data-quantity="20" data-unit="units">Park benches</li>
        </ul>
    </article>
    HTML;
}
