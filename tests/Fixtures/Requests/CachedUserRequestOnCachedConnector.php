<?php

namespace Saloon\CachePlugin\Tests\Fixtures\Requests;

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Saloon\CachePlugin\Contracts\Driver;
use Saloon\CachePlugin\Drivers\FlysystemDriver;
use Saloon\CachePlugin\Tests\Fixtures\Connectors\CachedConnector;
use Saloon\CachePlugin\Traits\AlwaysCacheResponses;
use Sammyjo20\Saloon\Constants\Saloon;
use Sammyjo20\Saloon\Http\SaloonRequest;

class CachedUserRequestOnCachedConnector extends SaloonRequest
{
    use AlwaysCacheResponses;

    protected ?string $connector = CachedConnector::class;

    protected ?string $method = Saloon::GET;

    public function defineEndpoint(): string
    {
        return '/user';
    }

    public function cacheDriver(): Driver
    {
        return new FlysystemDriver(new Filesystem(new LocalFilesystemAdapter(cachePath() . '/custom')));
    }

    public function cacheTTLInSeconds(): int
    {
        return 30;
    }
}
