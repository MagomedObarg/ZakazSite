<?php

use App\Application;
use App\Http\Request;
use App\Services\CacheService;
use Tests\Support\FakeAiClient;
use Tests\Support\FakeAnalysisResultStore;
use Tests\Support\FakeProcurementFetcher;

function make_application(): Application
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
        'summary' => 'Park refurbishments must manage stakeholder expectations.',
        'risks' => ['Tight delivery timeline'],
        'recommendations' => ['Include phased milestones'],
        'score' => 0.92,
        'meta' => ['model' => 'deepseek-chat'],
    ];

    return Application::create([
        'cache' => new CacheService(),
        'procurementFetcher' => new FakeProcurementFetcher($procurement),
        'aiClient' => new FakeAiClient($aiResponse),
        'resultStore' => new FakeAnalysisResultStore(),
    ]);
}

it('renders the landing page with default view data', function () {
    $app = make_application();

    $response = $app->handle(Request::fromArray([
        'method' => 'GET',
        'path' => '/',
    ]));

    expect($response->isView())->toBeTrue()
        ->and($response->view())->toBe('home')
        ->and($response->data()['title'])->toBe('Procurement Insight Analyzer');
});

it('processes form submissions and redirects to the result page with a flash message', function () {
    $app = make_application();

    $response = $app->handle(Request::fromArray([
        'method' => 'POST',
        'path' => '/analyze',
        'input' => ['procurement_url' => 'https://procure.test/tender/42'],
    ]));

    expect($response->isRedirect())->toBeTrue()
        ->and($response->redirectTo())->toStartWith('/result/');

    $flash = $app->session()->peekFlash();
    expect($flash['status'])->toContain('Analysis completed')
        ->and($flash['analysis_id'])->not()->toBeEmpty();

    $resultId = $flash['analysis_id'];
    $result = $app->analysis()->find($resultId);
    expect($result)->not()->toBeNull();
});

it('validates procurement URLs on the analyze form', function () {
    $app = make_application();

    $response = $app->handle(Request::fromArray([
        'method' => 'POST',
        'path' => '/analyze',
        'input' => ['procurement_url' => 'not-a-url'],
    ]));

    expect($response->isRedirect())->toBeTrue()
        ->and($response->redirectTo())->toBe('/');

    $errors = $app->session()->peekFlash('errors');
    expect($errors['procurement_url'])->toContain('valid procurement notice URL');
});

it('shows a rendered analysis result when it exists', function () {
    $app = make_application();

    $app->handle(Request::fromArray([
        'method' => 'POST',
        'path' => '/analyze',
        'input' => ['procurement_url' => 'https://procure.test/tender/43'],
    ]));

    $resultId = $app->session()->peekFlash('analysis_id');

    $response = $app->handle(Request::fromArray([
        'method' => 'GET',
        'path' => '/result/' . $resultId,
    ]));

    expect($response->isView())->toBeTrue()
        ->and($response->view())->toBe('result.show')
        ->and($response->data()['result']['id'])->toBe($resultId);
});

it('returns a 404 view when an analysis result cannot be found', function () {
    $app = make_application();

    $response = $app->handle(Request::fromArray([
        'method' => 'GET',
        'path' => '/result/missing-id',
    ]));

    expect($response->status())->toBe(404)
        ->and($response->view())->toBe('result.missing');
});
