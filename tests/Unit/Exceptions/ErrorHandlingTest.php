<?php

use App\Exceptions\CachedErrorException;
use App\Exceptions\DeepSeekException;
use App\Exceptions\Handler;
use App\Exceptions\ProcurementNotFoundException;
use App\Exceptions\ProcurementParseException;
use App\Http\Request;

it('handles ProcurementNotFoundException with 404 status', function () {
    $handler = new Handler();
    $exception = new ProcurementNotFoundException();

    $request = Request::fromArray([
        'method' => 'POST',
        'path' => '/api/analyze',
    ]);

    $response = $handler->render($request, $exception);

    expect($response->status())->toBe(404)
        ->and($response->jsonData()['status'])->toBe('error')
        ->and($response->jsonData()['error_code'])->toBe('PROCUREMENT_NOT_FOUND')
        ->and($response->jsonData()['message'])->toContain('could not be found');
});

it('handles ProcurementParseException with 422 status', function () {
    $handler = new Handler();
    $exception = new ProcurementParseException('Invalid document structure');

    $request = Request::fromArray([
        'method' => 'POST',
        'path' => '/api/analyze',
    ]);

    $response = $handler->render($request, $exception);

    expect($response->status())->toBe(422)
        ->and($response->jsonData()['error_code'])->toBe('PROCUREMENT_PARSE_ERROR');
});

it('handles DeepSeekException with 502 status', function () {
    $handler = new Handler();
    $exception = new DeepSeekException('API timeout');

    $request = Request::fromArray([
        'method' => 'POST',
        'path' => '/api/analyze',
    ]);

    $response = $handler->render($request, $exception);

    expect($response->status())->toBe(502)
        ->and($response->jsonData()['error_code'])->toBe('AI_SERVICE_ERROR')
        ->and($response->jsonData()['message'])->toContain('temporarily unavailable');
});

it('handles CachedErrorException with stored status code', function () {
    $errorData = [
        'is_error' => true,
        'status_code' => 404,
        'error_code' => 'PROCUREMENT_NOT_FOUND',
        'user_message' => 'Cached error message',
        'cached_at' => date('Y-m-d H:i:s'),
    ];

    $handler = new Handler();
    $exception = new CachedErrorException($errorData);

    $request = Request::fromArray([
        'method' => 'POST',
        'path' => '/api/analyze',
    ]);

    $response = $handler->render($request, $exception);

    expect($response->status())->toBe(404)
        ->and($response->jsonData()['error_code'])->toBe('PROCUREMENT_NOT_FOUND')
        ->and($response->jsonData()['message'])->toBe('Cached error message');
});

it('handles RuntimeException with 400 status for backwards compatibility', function () {
    $handler = new Handler();
    $exception = new RuntimeException('Invalid input data');

    $request = Request::fromArray([
        'method' => 'POST',
        'path' => '/api/analyze',
    ]);

    $response = $handler->render($request, $exception);

    expect($response->status())->toBe(400)
        ->and($response->jsonData()['message'])->toBe('Invalid input data');
});

it('redirects web requests to home page with error flash', function () {
    $handler = new Handler();
    $exception = new ProcurementNotFoundException();

    $request = Request::fromArray([
        'method' => 'POST',
        'path' => '/analyze',
    ]);

    $response = $handler->render($request, $exception);

    expect($response->isRedirect())->toBeTrue()
        ->and($response->redirectTo())->toBe('/')
        ->and($response->flash()['errors']['general'])->toContain('could not be found');
});

it('detects API requests via path prefix', function () {
    $handler = new Handler();
    $exception = new ProcurementNotFoundException();

    $apiRequest = Request::fromArray([
        'method' => 'POST',
        'path' => '/api/analyze',
    ]);

    $webRequest = Request::fromArray([
        'method' => 'POST',
        'path' => '/analyze',
    ]);

    $apiResponse = $handler->render($apiRequest, $exception);
    $webResponse = $handler->render($webRequest, $exception);

    expect($apiResponse->isJson())->toBeTrue()
        ->and($webResponse->isRedirect())->toBeTrue();
});
