<?php

use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Clients\MockClient;
use Saloon\CachePlugin\Tests\Fixtures\Stores\ArrayCache;
use Saloon\CachePlugin\Tests\Fixtures\Requests\SimpleCachedUserRequest;

it('will return a cached response', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
        MockResponse::make(['name' => 'Gareth']),
    ]);

    $arrayCache = new ArrayCache();

    $requestA = new SimpleCachedUserRequest($arrayCache);
    $responseA = $requestA->send($mockClient);

    expect($responseA->isCached())->toBeFalse();
    expect($responseA->json())->toEqual(['name' => 'Sam']);

    $requestB = new SimpleCachedUserRequest($arrayCache);
    $responseB = $requestB->send($mockClient);

    expect($responseB->isCached())->toBeTrue();
    expect($responseB->header('X-Saloon-Cache'))->toEqual('Cached');
    expect($responseB->json())->toEqual(['name' => 'Sam']);

    expect($arrayCache->all())->toHaveCount(1);
});
