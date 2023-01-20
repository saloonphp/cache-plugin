<?php

use League\Flysystem\Filesystem;
use Saloon\CachePlugin\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Faking\MockClient;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Saloon\CachePlugin\Tests\Fixtures\Requests\CachedPostRequest;
use Saloon\CachePlugin\Tests\Fixtures\Requests\CachedUserRequest;
use Saloon\CachePlugin\Tests\Fixtures\Requests\CachedConnectorRequest;
use Saloon\CachePlugin\Tests\Fixtures\Requests\CustomKeyCachedUserRequest;
use Saloon\CachePlugin\Tests\Fixtures\Requests\ShortLifeCachedUserRequest;
use Saloon\CachePlugin\Tests\Fixtures\Requests\AdvancedCustomKeyCachedUserRequest;
use Saloon\CachePlugin\Tests\Fixtures\Requests\CachedUserRequestOnCachedConnector;

$filesystem = new Filesystem(new LocalFilesystemAdapter(cachePath()));

beforeEach(function () use ($filesystem) {
    $filesystem->deleteDirectory('/');
});

test('a request with the HasCaching trait will cache the response with a real request', function () {
    $responseA = TestConnector::make()->send(new CachedUserRequest);

    $responseBody = [
        'name' => 'Sammyjo20',
        'actual_name' => 'Sam',
        'twitter' => '@carre_sam'
    ];

    expect($responseA->isSimulated())->toBeFalse();
    expect($responseA->isCached())->toBeFalse();
    expect($responseA->json())->toEqual($responseBody);

    // Now send a response without the mock middleware, and it should be cached!

    $responseB = TestConnector::make()->send(new CachedUserRequest);

    expect($responseB->isSimulated())->toBeTrue();
    expect($responseB->isCached())->toBeTrue();
    expect($responseB->json())->toEqual($responseBody);
});

test('a request with the HasCaching trait will cache the response', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam'], 201, ['X-Howdy' => 'Yeehaw']),
    ]);

    $responseA = TestConnector::make()->send(new CachedUserRequest, $mockClient);

    expect($responseA->isCached())->toBeFalse();
    expect($responseA->status())->toEqual(201);
    expect($responseA->json())->toEqual(['name' => 'Sam']);
    expect($responseA->header('X-Howdy'))->toEqual('Yeehaw');

    // Now send a response without the mock middleware, and it should be cached!

    $responseB = TestConnector::make()->send(new CachedUserRequest);

    expect($responseB->isSimulated())->toBeTrue();
    expect($responseB->isCached())->toBeTrue();
    expect($responseB->status())->toEqual(201);
    expect($responseB->json())->toEqual(['name' => 'Sam']);
    expect($responseB->header('X-Howdy'))->toEqual('Yeehaw');
});

test('a request with the HasCaching trait will cache the response with string body', function () {
    $mockClient = new MockClient([
        MockResponse::make('<p>Hi</p>', 201, ['X-Howdy' => 'Yeehaw']),
    ]);

    $responseA = TestConnector::make()->send(new CachedUserRequest, $mockClient);

    expect($responseA->isCached())->toBeFalse();
    expect($responseA->status())->toEqual(201);
    expect($responseA->body())->toEqual('<p>Hi</p>');
    expect($responseA->header('X-Howdy'))->toEqual('Yeehaw');

    // Now send a response without the mock middleware, and it should be cached!

    $responseB = TestConnector::make()->send(new CachedUserRequest);

    expect($responseB->isSimulated())->toBeTrue();
    expect($responseB->isCached())->toBeTrue();
    expect($responseB->status())->toEqual(201);
    expect($responseB->body())->toEqual('<p>Hi</p>');
    expect($responseB->header('X-Howdy'))->toEqual('Yeehaw');
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

test('query parameters are used in the cache key', function () use ($filesystem) {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
        MockResponse::make(['name' => 'Sam']),
        MockResponse::make(['name' => 'Sam']),
    ]);

    $requestA = new CachedUserRequest();
    $requestA->addQuery('name', 'Sam');
    $responseA = $requestA->send($mockClient);

    expect($responseA->isCached())->toBeFalse();
    expect($responseA->json())->toEqual(['name' => 'Sam']);
    expect($responseA->status())->toEqual(200);

    $requestB = new CachedUserRequest();
    $requestB->addQuery('name', 'Sam');
    $responseB = $requestB->send($mockClient);

    expect($responseB->isCached())->toBeTrue();
    expect($responseB->json())->toEqual(['name' => 'Sam']);
    expect($responseB->status())->toEqual(200);

    $requestC = new CachedUserRequest();
    $responseC = $requestC->send($mockClient);

    expect($responseC->isCached())->toBeFalse();
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

test('cache can be invalidated', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
        MockResponse::make(['name' => 'Gareth']),
        MockResponse::make(['name' => 'Teo']),
        MockResponse::make(['name' => 'Mantas']),
    ]);

    $requestA = new CachedUserRequest();
    $responseA = $requestA->send($mockClient);

    expect($responseA->isCached())->toBeFalse();
    expect($responseA->json())->toEqual(['name' => 'Sam']);

    $requestB = new CachedUserRequest();
    $responseB = $requestB->send($mockClient);

    // The response should now be cached...

    expect($responseB->isCached())->toBeTrue();
    expect($responseB->json())->toEqual(['name' => 'Sam']);

    $requestC = new CachedUserRequest();
    $requestC->invalidateCache();
    $responseC = $requestC->send($mockClient);

    expect($responseC->isCached())->toBeFalse();
    expect($responseC->json())->toEqual(['name' => 'Teo']);

    // Now just make sure that the new response is cached...

    $requestD = new CachedUserRequest();
    $responseD = $requestD->send($mockClient);

    expect($responseD->isCached())->toBeTrue();
    expect($responseD->json())->toEqual(['name' => 'Teo']);
});
