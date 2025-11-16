<?php

use App\Services\CacheService;

it('stores and retrieves cached values until they expire', function () {
    $time = 0.0;
    $cache = new CacheService(fn () => $time);

    $cache->put('greeting', 'hello', 10);
    expect($cache->has('greeting'))->toBeTrue();
    expect($cache->get('greeting'))->toBe('hello');

    $time = 11.0;
    expect($cache->has('greeting'))->toBeFalse();
    expect($cache->get('greeting'))->toBeNull();
});

it('only computes expensive values once when using remember', function () {
    $invocations = 0;
    $cache = new CacheService();

    $value = $cache->remember('number', 60, function () use (&$invocations) {
        $invocations++;

        return 42;
    });

    $again = $cache->remember('number', 60, function () use (&$invocations) {
        $invocations++;

        return 100;
    });

    expect($value)->toBe(42)
        ->and($again)->toBe(42)
        ->and($invocations)->toBe(1);
});
