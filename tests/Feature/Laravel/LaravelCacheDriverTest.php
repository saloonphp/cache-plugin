<?php

declare(strict_types=1);

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Illuminate\Support\Facades\Cache;
use Saloon\CachePlugin\Tests\Fixtures\Connectors\TestConnector;
use Saloon\CachePlugin\Tests\Fixtures\Requests\LaravelCachedUserRequest;

beforeEach(function () {
    Cache::store('file')->clear();
});

it('will return a cached response', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
    ]);

    $connector = new TestConnector;

    $requestA = new LaravelCachedUserRequest();
    $responseA = $connector->send($requestA, $mockClient);

    expect($responseA->isCached())->toBeFalse();
    expect($responseA->json())->toEqual(['name' => 'Sam']);

    $requestB = new LaravelCachedUserRequest();
    $responseB = $connector->send($requestB);

    expect($responseB->isCached())->toBeTrue();
    expect($responseB->json())->toEqual(['name' => 'Sam']);
});
