<?php

declare(strict_types=1);

namespace Saloon\CachePlugin\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;
use League\Flysystem\Filesystem;
use Saloon\CachePlugin\Contracts\Driver;
use Saloon\CachePlugin\Drivers\FlysystemDriver;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Saloon\CachePlugin\Traits\AlwaysCacheResponses;

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
