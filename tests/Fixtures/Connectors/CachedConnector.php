<?php

namespace Sammyjo20\SaloonCachePlugin\Tests\Fixtures\Connectors;

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Sammyjo20\Saloon\Http\SaloonConnector;
use Sammyjo20\SaloonCachePlugin\Drivers\FlysystemDriver;
use Sammyjo20\SaloonCachePlugin\Interfaces\CacheDriver;
use Sammyjo20\SaloonCachePlugin\Traits\AlwaysCacheResponses;

class CachedConnector extends SaloonConnector
{
    use AlwaysCacheResponses;

    public function defineBaseUrl(): string
    {
        return testApi();
    }

    public function cacheDriver(): CacheDriver
    {
        return new FlysystemDriver(new Filesystem(new LocalFilesystemAdapter(cachePath())));
    }

    public function cacheTTLInSeconds(): int
    {
        return 60;
    }
}
