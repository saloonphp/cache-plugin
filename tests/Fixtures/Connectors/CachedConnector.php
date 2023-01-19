<?php

namespace Saloon\CachePlugin\Tests\Fixtures\Connectors;

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Saloon\CachePlugin\Contracts\Driver;
use Saloon\CachePlugin\Drivers\FlysystemDriver;
use Saloon\CachePlugin\Traits\AlwaysCacheResponses;
use Saloon\Http\Connector;

class CachedConnector extends Connector
{
    use AlwaysCacheResponses;

    public function resolveBaseUrl(): string
    {
        return testApi();
    }

    public function cacheDriver(): Driver
    {
        return new FlysystemDriver(new Filesystem(new LocalFilesystemAdapter(cachePath())));
    }

    public function cacheTTLInSeconds(): int
    {
        return 60;
    }
}
