<?php

declare(strict_types=1);

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Saloon\CachePlugin\Tests\Fixtures\Connectors\TestConnector;
use Saloon\CachePlugin\Tests\Fixtures\Requests\CachedUserRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

$filesystem = new Filesystem(new LocalFilesystemAdapter(cachePath()));

beforeEach(function () use ($filesystem): void {
    $filesystem->deleteDirectory('/');
});

function cachedUserRequestFixtureAbsolutePath(): string
{
    $dir = getcwd().'/tests/Fixtures/Saloon';

    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    return $dir.'/cached-user-request.json';
}

function writeCachedUserRequestFixture(int $version): void
{
    $payload = [
        'statusCode' => 200,
        'headers' => ['Content-Type' => ['application/json']],
        'data' => json_encode(['version' => $version]),
        'context' => [],
    ];

    file_put_contents(
        cachedUserRequestFixtureAbsolutePath(),
        json_encode($payload, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)
    );
}

test('response cache can return a stale body when mocks use fixtures and withoutCache is not used', function (): void {
    writeCachedUserRequestFixture(1);

    $mockClient = new MockClient([
        CachedUserRequest::class => MockResponse::fixture('cached-user-request'),
    ]);

    $connector = TestConnector::make();

    expect($connector->send(new CachedUserRequest, $mockClient)->json('version'))->toBe(1);

    writeCachedUserRequestFixture(2);

    expect($connector->send(new CachedUserRequest, $mockClient)->json('version'))->toBe(1);
});

test('withoutCache on the mock client skips response cache so updated fixture files are used', function (): void {
    writeCachedUserRequestFixture(1);

    $mockClient = (new MockClient([
        CachedUserRequest::class => MockResponse::fixture('cached-user-request'),
    ]))->withoutCache();

    $connector = TestConnector::make();

    expect($connector->send(new CachedUserRequest, $mockClient)->json('version'))->toBe(1);

    writeCachedUserRequestFixture(2);

    expect($connector->send(new CachedUserRequest, $mockClient)->json('version'))->toBe(2);
});
