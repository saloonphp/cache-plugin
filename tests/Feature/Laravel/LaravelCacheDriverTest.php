<?php

use Illuminate\Support\Facades\Cache;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\SaloonCachePlugin\Tests\Fixtures\Requests\LaravelCachedUserRequest;

beforeEach(function () {
    Cache::store('file')->clear();
});

it('will return a cached response', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
        MockResponse::make(['name' => 'Gareth']),
    ]);

    $requestA = new LaravelCachedUserRequest();
    $responseA = $requestA->send($mockClient);

    expect($responseA->isCached())->toBeFalse();
    expect($responseA->json())->toEqual(['name' => 'Sam']);

    $requestB = new LaravelCachedUserRequest();
    $responseB = $requestB->send($mockClient);

    expect($responseB->isCached())->toBeTrue();
    expect($responseB->header('X-Saloon-Cache'))->toEqual('Cached');
    expect($responseB->json())->toEqual(['name' => 'Sam']);
});
