<?php

namespace Sammyjo20\SaloonCachePlugin\Tests\Fixtures\Connectors;

use League\Flysystem\Filesystem;
use Sammyjo20\Saloon\Http\SaloonConnector;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Sammyjo20\SaloonCachePlugin\Interfaces\CacheDriverInterface;
use Sammyjo20\SaloonCachePlugin\Drivers\FlysystemDriverInterface;
use Sammyjo20\SaloonCachePlugin\Traits\AlwaysCacheResponses;

class CachedConnector extends SaloonConnector
{
    use AlwaysCacheResponses;

    public function defineBaseUrl(): string
    {
        return testApi();
    }

    public function cacheDriver(): CacheDriverInterface
    {
        return new FlysystemDriverInterface(new Filesystem(new LocalFilesystemAdapter(cachePath())));
    }

    public function cacheTTLInSeconds(): int
    {
        return 60;
    }
}
