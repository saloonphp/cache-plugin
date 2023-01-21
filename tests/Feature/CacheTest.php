<?php

declare(strict_types=1);

use League\Flysystem\Filesystem;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Saloon\CachePlugin\Exceptions\HasCachingException;
use Saloon\CachePlugin\Tests\Fixtures\Connectors\TestConnector;
use Saloon\CachePlugin\Tests\Fixtures\Connectors\CachedConnector;
use Saloon\CachePlugin\Tests\Fixtures\Requests\CachedPostRequest;
use Saloon\CachePlugin\Tests\Fixtures\Requests\CachedUserRequest;
use Saloon\CachePlugin\Tests\Fixtures\Requests\CachedConnectorRequest;
use Saloon\CachePlugin\Tests\Fixtures\Requests\CustomKeyCachedUserRequest;
use Saloon\CachePlugin\Tests\Fixtures\Requests\ShortLivedCachedUserRequest;
use Saloon\CachePlugin\Tests\Fixtures\Requests\CachedUserRequestWithoutCacheable;
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
        'twitter' => '@carre_sam',
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

    $responseA = TestConnector::make()->send(new CachedPostRequest, $mockClient);

    expect($responseA->isCached())->toBeFalse();
    expect($responseA->json())->toEqual(['name' => 'Sam']);

    $responseB = TestConnector::make()->send(new CachedPostRequest, $mockClient);

    expect($responseB->isCached())->toBeFalse();
    expect($responseB->json())->toEqual(['name' => 'Gareth']);
});

test('a response will not be cached if the response was not 2xx', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam'], 422),
        MockResponse::make(['name' => 'Gareth'], 500),
    ]);

    $responseA = TestConnector::make()->send(new CachedUserRequest, $mockClient);

    expect($responseA->isCached())->toBeFalse();
    expect($responseA->json())->toEqual(['name' => 'Sam']);
    expect($responseA->status())->toEqual(422);

    $responseB = TestConnector::make()->send(new CachedUserRequest, $mockClient);

    expect($responseB->isCached())->toBeFalse();
    expect($responseB->json())->toEqual(['name' => 'Gareth']);
    expect($responseB->status())->toEqual(500);
});

test('a custom cache key can be provided on the response', function () use ($filesystem) {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
    ]);

    TestConnector::make()->send(new CustomKeyCachedUserRequest, $mockClient);

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
    $requestA->query()->add('name', 'Sam');
    $responseA = TestConnector::make()->send($requestA, $mockClient);

    expect($responseA->isCached())->toBeFalse();
    expect($responseA->json())->toEqual(['name' => 'Sam']);
    expect($responseA->status())->toEqual(200);

    $requestB = new CachedUserRequest();
    $requestB->query()->add('name', 'Sam');
    $responseB = TestConnector::make()->send($requestB);

    expect($responseB->isCached())->toBeTrue();
    expect($responseB->json())->toEqual(['name' => 'Sam']);
    expect($responseB->status())->toEqual(200);

    $requestC = new CachedUserRequest();
    $responseC = TestConnector::make()->send($requestC, $mockClient);

    expect($responseC->isCached())->toBeFalse();
});

test('you will not receive a cached response if the response has expired', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
        MockResponse::make(['name' => 'Michael']),
    ]);

    $connector = new TestConnector;

    $requestA = new ShortLivedCachedUserRequest();
    $responseA = $connector->send($requestA, $mockClient);

    expect($responseA->isCached())->toBeFalse();
    expect($responseA->json())->toEqual(['name' => 'Sam']);

    $requestB = new ShortLivedCachedUserRequest();
    $responseB = $connector->send($requestB);

    expect($responseB->isCached())->toBeTrue();
    expect($responseB->json())->toEqual(['name' => 'Sam']);

    sleep(3);

    $requestC = new ShortLivedCachedUserRequest();
    $responseC = $connector->send($requestC, $mockClient);

    expect($responseC->isCached())->toBeFalse();
    expect($responseC->json())->toEqual(['name' => 'Michael']);
});

test('you can define a cache on the connector and it returns a cached response', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
    ]);

    $connector = new CachedConnector;

    $requestA = new CachedConnectorRequest();
    $responseA = $connector->send($requestA, $mockClient);

    expect($responseA->isCached())->toBeFalse();
    expect($responseA->json())->toEqual(['name' => 'Sam']);

    $requestB = new CachedConnectorRequest();
    $responseB = $connector->send($requestB);

    expect($responseB->isCached())->toBeTrue();
    expect($responseB->json())->toEqual(['name' => 'Sam']);
});

test('if a request has cache configuration then it will take priority over the connectors', function () use ($filesystem) {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
    ]);

    $connector = new CachedConnector;

    $requestA = new CachedUserRequestOnCachedConnector();
    $responseA = $connector->send($requestA, $mockClient);

    expect($responseA->isCached())->toBeFalse();
    expect($responseA->json())->toEqual(['name' => 'Sam']);

    $requestB = new CachedUserRequestOnCachedConnector();
    $responseB = $connector->send($requestB);

    expect($responseB->isCached())->toBeTrue();
    expect($responseB->json())->toEqual(['name' => 'Sam']);

    expect($filesystem->directoryExists('custom'))->toBeTrue();
    expect(count($filesystem->listContents('custom')->toArray()))->toEqual(1);
});

test('you can disable the cache', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
        MockResponse::make(['name' => 'Michael']),
    ]);

    $connector = new TestConnector;

    $requestA = new CachedUserRequest();
    $responseA = $connector->send($requestA, $mockClient);

    expect($responseA->isCached())->toBeFalse();
    expect($responseA->json())->toEqual(['name' => 'Sam']);

    $requestB = new CachedUserRequest();
    $responseB = $connector->send($requestB);

    expect($responseB->isCached())->toBeTrue();
    expect($responseB->json())->toEqual(['name' => 'Sam']);

    $requestC = new CachedUserRequest();
    $requestC->disableCaching();

    $responseC = $connector->send($requestC, $mockClient);

    expect($responseC->isCached())->toBeFalse();
    expect($responseC->json())->toEqual(['name' => 'Michael']);
});

test('cache can be invalidated', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
        MockResponse::make(['name' => 'Teo']),
    ]);

    $connector = new TestConnector;

    $requestA = new CachedUserRequest();
    $responseA = $connector->send($requestA, $mockClient);

    expect($responseA->isCached())->toBeFalse();
    expect($responseA->json())->toEqual(['name' => 'Sam']);

    $requestB = new CachedUserRequest();
    $responseB = $connector->send($requestB);

    // The response should now be cached...

    expect($responseB->isCached())->toBeTrue();
    expect($responseB->json())->toEqual(['name' => 'Sam']);

    $requestC = new CachedUserRequest();
    $requestC->invalidateCache();
    $responseC = $connector->send($requestC, $mockClient);

    expect($responseC->isCached())->toBeFalse();
    expect($responseC->json())->toEqual(['name' => 'Teo']);

    // Now just make sure that the new response is cached...

    $requestD = new CachedUserRequest();
    $responseD = $connector->send($requestD);

    expect($responseD->isCached())->toBeTrue();
    expect($responseD->json())->toEqual(['name' => 'Teo']);
});

test('it throws an exception if you use the HasCaching trait without the Cacheable interface', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
    ]);

    $connector = new TestConnector;
    $request = new CachedUserRequestWithoutCacheable;

    $this->expectException(HasCachingException::class);
    $this->expectExceptionMessage('Your connector or request must implement Saloon\CachePlugin\Contracts\Cacheable to use the HasCaching plugin');

    $connector->send($request, $mockClient);
});
