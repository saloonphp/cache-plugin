<?php

use League\Flysystem\Filesystem;
use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Clients\MockClient;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Sammyjo20\SaloonCachePlugin\Tests\Fixtures\Requests\CachedPostRequest;
use Sammyjo20\SaloonCachePlugin\Tests\Fixtures\Requests\CachedUserRequest;
use Sammyjo20\SaloonCachePlugin\Tests\Fixtures\Requests\CachedConnectorRequest;
use Sammyjo20\SaloonCachePlugin\Tests\Fixtures\Requests\CustomKeyCachedUserRequest;
use Sammyjo20\SaloonCachePlugin\Tests\Fixtures\Requests\ShortLifeCachedUserRequest;
use Sammyjo20\SaloonCachePlugin\Tests\Fixtures\Requests\AdvancedCustomKeyCachedUserRequest;
use Sammyjo20\SaloonCachePlugin\Tests\Fixtures\Requests\CachedUserRequestOnCachedConnector;

$filesystem = new Filesystem(new LocalFilesystemAdapter(cachePath()));

beforeEach(function () use ($filesystem) {
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
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
        MockResponse::make(['name' => 'Gareth']),
    ]);

    $requestA = new CachedPostRequest();
    $responseA = $requestA->send($mockClient);

    expect($responseA->isCached())->toBeFalse();
    expect($responseA->json())->toEqual(['name' => 'Sam']);

    $requestB = new CachedUserRequest();
    $responseB = $requestB->send($mockClient);

    expect($responseB->isCached())->toBeFalse();
    expect($responseB->json())->toEqual(['name' => 'Gareth']);
});

test('a response will not be cached if the response was not 2xx', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam'], 422),
        MockResponse::make(['name' => 'Sam'], 500),
    ]);

    $requestA = new CachedUserRequest();
    $responseA = $requestA->send($mockClient);

    expect($responseA->isCached())->toBeFalse();
    expect($responseA->json())->toEqual(['name' => 'Sam']);
    expect($responseA->status())->toEqual(422);

    $requestB = new CachedUserRequest();
    $responseB = $requestB->send($mockClient);

    expect($responseB->isCached())->toBeFalse();
    expect($responseB->json())->toEqual(['name' => 'Sam']);
    expect($responseB->status())->toEqual(500);
});

test('a custom cache key can be provided on the response', function () use ($filesystem) {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
    ]);

    $requestA = new CustomKeyCachedUserRequest();
    $responseA = $requestA->send($mockClient);

    $hash = hash('sha256', 'Howdy!');

    expect($filesystem->fileExists($hash))->toBeTrue();
});

test('the generation of the custom key can be overwritten', function () use ($filesystem) {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
    ]);

    $requestA = new AdvancedCustomKeyCachedUserRequest();
    $responseA = $requestA->send($mockClient);

    $filesystem = new Filesystem(new LocalFilesystemAdapter(cachePath()));

    expect($filesystem->fileExists('Howdy!'))->toBeTrue();
});

test('you will not receive a cached response if the response has expired', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
        MockResponse::make(['name' => 'Gareth']),
        MockResponse::make(['name' => 'Michael']),
    ]);

    $requestA = new ShortLifeCachedUserRequest();
    $responseA = $requestA->send($mockClient);

    expect($responseA->isCached())->toBeFalse();
    expect($responseA->json())->toEqual(['name' => 'Sam']);

    $requestB = new ShortLifeCachedUserRequest();
    $responseB = $requestB->send($mockClient);

    expect($responseB->isCached())->toBeTrue();
    expect($responseB->json())->toEqual(['name' => 'Sam']);

    sleep(3);

    $requestC = new ShortLifeCachedUserRequest();
    $responseC = $requestB->send($mockClient);

    expect($responseC->isCached())->toBeFalse();
    expect($responseC->json())->toEqual(['name' => 'Michael']);
});

test('you can define a cache on the connector and it returns a cached response', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
        MockResponse::make(['name' => 'Gareth']),
    ]);

    $requestA = new CachedConnectorRequest();
    $responseA = $requestA->send($mockClient);

    expect($responseA->isCached())->toBeFalse();
    expect($responseA->json())->toEqual(['name' => 'Sam']);

    $requestB = new CachedConnectorRequest();
    $responseB = $requestB->send($mockClient);

    expect($responseB->isCached())->toBeTrue();
    expect($responseB->json())->toEqual(['name' => 'Sam']);
});

test('if a request has cache configuration then it will take priority over the connectors', function () use ($filesystem) {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
        MockResponse::make(['name' => 'Gareth']),
    ]);

    $requestA = new CachedUserRequestOnCachedConnector();
    $responseA = $requestA->send($mockClient);

    expect($responseA->isCached())->toBeFalse();
    expect($responseA->json())->toEqual(['name' => 'Sam']);

    $requestB = new CachedUserRequestOnCachedConnector();
    $responseB = $requestB->send($mockClient);

    expect($responseB->isCached())->toBeTrue();
    expect($responseB->json())->toEqual(['name' => 'Sam']);

    expect($filesystem->directoryExists('custom'))->toBeTrue();
    expect(count($filesystem->listContents('custom')->toArray()))->toEqual(1);
});

test('you can disable the cache', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
        MockResponse::make(['name' => 'Gareth']),
        MockResponse::make(['name' => 'Michael']),
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

    $requestC = new CachedUserRequest();
    $requestC->disableCaching();

    $responseC = $requestC->send($mockClient);

    expect($responseC->isCached())->toBeFalse();
    expect($responseC->json())->toEqual(['name' => 'Michael']);
});
