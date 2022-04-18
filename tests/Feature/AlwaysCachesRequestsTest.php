<?php

use League\Flysystem\Filesystem;
use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Clients\MockClient;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Sammyjo20\SaloonCachePlugin\Tests\Fixtures\Requests\CachedUserRequest;

beforeEach(function () {
    $filesystem = new Filesystem(new LocalFilesystemAdapter(cachePath()));
    $filesystem->deleteDirectory('/');
});

test('a request with the AlwaysCachesRequests trait will cache the response', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
        MockResponse::make(['name' => 'Gareth']),
    ]);

    $requestA = new CachedUserRequest();
    $responseA = $requestA->send($mockClient);

    expect($responseA->isCached())->toBeFalse();
    expect($responseA->json())->toEqual(['name' => 'Sam']);

    $requestB = new CachedUserRequest();
    $responseB = $requestB->send($mockClient);

    expect($responseB->isCached())->toBeTrue();
    expect($responseB->header('X-Saloon-Cache'))->toEqual('Cached');
    expect($responseB->json())->toEqual(['name' => 'Sam']);
});

test('it wont cache on anything other than GET and OPTIONS', function () {
    //
});

test('a response will not be cached if the response was not 2xx', function () {
    //
});

test('a custom cache key can be provided on the response', function () {
    //
});

test('you will not recieve a cached response if the response has expired', function () {
    //
});
