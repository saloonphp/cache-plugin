<?php

declare(strict_types=1);

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\CachePlugin\Tests\Fixtures\Stores\ArrayCache;
use Saloon\CachePlugin\Tests\Fixtures\Connectors\TestConnector;
use Saloon\CachePlugin\Tests\Fixtures\Requests\PsrCachedUserRequest;

it('will return a cached response', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
        MockResponse::make(['name' => 'Gareth']),
    ]);

    $arrayCache = new ArrayCache();

    $connector = new TestConnector;

    $requestA = new PsrCachedUserRequest($arrayCache);
    $responseA = $connector->send($requestA, $mockClient);

    expect($responseA->isCached())->toBeFalse();
    expect($responseA->json())->toEqual(['name' => 'Sam']);

    $requestB = new PsrCachedUserRequest($arrayCache);
    $responseB = $connector->send($requestB);

    expect($responseB->isCached())->toBeTrue();
    expect($responseB->json())->toEqual(['name' => 'Sam']);

    expect($arrayCache->all())->toHaveCount(1);
});
