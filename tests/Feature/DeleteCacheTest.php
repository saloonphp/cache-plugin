<?php

declare(strict_types=1);

use League\Flysystem\Filesystem;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Saloon\CachePlugin\Tests\Fixtures\Connectors\TestConnector;
use Saloon\CachePlugin\Tests\Fixtures\Connectors\CachedConnector;
use Saloon\CachePlugin\Tests\Fixtures\Requests\CachedUserRequest;
use Saloon\CachePlugin\Tests\Fixtures\Requests\CachedConnectorRequest;
use Saloon\CachePlugin\Tests\Fixtures\Requests\CustomKeyCachedUserRequest;

$filesystem = new Filesystem(new LocalFilesystemAdapter(cachePath()));

beforeEach(function () use ($filesystem) {
    $filesystem->deleteDirectory('/');
});

test('deleteCache removes a cached response without sending a request', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
    ]);

    $connector = new TestConnector;
    $request = new CachedUserRequest;

    // Send and cache the response
    $responseA = $connector->send($request, $mockClient);
    expect($responseA->isCached())->toBeFalse();

    // Verify it is cached
    $responseB = $connector->send(new CachedUserRequest);
    expect($responseB->isCached())->toBeTrue();

    // Delete the cache without sending a request
    $request = new CachedUserRequest;
    $request->deleteCache($connector);

    // Now sending should result in a cache miss
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Michael']),
    ]);

    $responseC = $connector->send(new CachedUserRequest, $mockClient);
    expect($responseC->isCached())->toBeFalse();
    expect($responseC->json())->toEqual(['name' => 'Michael']);
});

test('deleteCache on an uncached request does not throw', function () {
    $connector = new TestConnector;
    $request = new CachedUserRequest;

    // Should not throw
    $request->deleteCache($connector);

    expect(true)->toBeTrue();
});

test('deleteCache uses a custom cacheKey override', function () use ($filesystem) {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
    ]);

    $connector = new TestConnector;

    // Send and cache with the custom key
    $connector->send(new CustomKeyCachedUserRequest, $mockClient);

    $hash = hash('sha256', 'Howdy!');
    expect($filesystem->fileExists($hash))->toBeTrue();

    // Delete using the custom key
    $request = new CustomKeyCachedUserRequest;
    $request->deleteCache($connector);

    expect($filesystem->fileExists($hash))->toBeFalse();
});

test('after deleteCache the next send fetches fresh and repopulates cache', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
    ]);

    $connector = new TestConnector;

    // Send and cache
    $connector->send(new CachedUserRequest, $mockClient);

    // Confirm cached
    $responseB = $connector->send(new CachedUserRequest);
    expect($responseB->isCached())->toBeTrue();
    expect($responseB->json())->toEqual(['name' => 'Sam']);

    // Delete cache
    $request = new CachedUserRequest;
    $request->deleteCache($connector);

    // Send again - should be a fresh response
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Teo']),
    ]);

    $responseC = $connector->send(new CachedUserRequest, $mockClient);
    expect($responseC->isCached())->toBeFalse();
    expect($responseC->json())->toEqual(['name' => 'Teo']);

    // Verify the new response is cached
    $responseD = $connector->send(new CachedUserRequest);
    expect($responseD->isCached())->toBeTrue();
    expect($responseD->json())->toEqual(['name' => 'Teo']);
});

test('deleteCache works when called from the connector', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
    ]);

    $connector = new CachedConnector;

    // Send and cache
    $connector->send(new CachedConnectorRequest, $mockClient);

    // Confirm cached
    $responseB = $connector->send(new CachedConnectorRequest);
    expect($responseB->isCached())->toBeTrue();

    // Delete cache from the connector side
    $connector->deleteCache(new CachedConnectorRequest);

    // Should be a cache miss now
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Michael']),
    ]);

    $responseC = $connector->send(new CachedConnectorRequest, $mockClient);
    expect($responseC->isCached())->toBeFalse();
    expect($responseC->json())->toEqual(['name' => 'Michael']);
});
