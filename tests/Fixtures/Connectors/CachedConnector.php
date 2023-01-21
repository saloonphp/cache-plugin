<?php

declare(strict_types=1);

namespace Saloon\CachePlugin\Tests\Fixtures\Connectors;

use Saloon\CachePlugin\Contracts\Cacheable;
use Saloon\CachePlugin\Traits\HasCaching;
use Saloon\Http\Connector;
use League\Flysystem\Filesystem;
use Saloon\CachePlugin\Contracts\Driver;
use Saloon\CachePlugin\Drivers\FlysystemDriver;
use League\Flysystem\Local\LocalFilesystemAdapter;

class CachedConnector extends Connector implements Cacheable
{
    use HasCaching;

    public function resolveBaseUrl(): string
    {
        return testApi();
    }

    public function resolveCacheDriver(): Driver
    {
        return new FlysystemDriver(new Filesystem(new LocalFilesystemAdapter(cachePath())));
    }

    public function cacheExpiryInSeconds(): int
    {
        return 60;
    }
}
