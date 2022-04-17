<?php

namespace Sammyjo20\SaloonCachePlugin\Tests\Fixtures\Requests;

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Sammyjo20\Saloon\Constants\Saloon;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\SaloonCachePlugin\Drivers\FlysystemDriver;
use Sammyjo20\SaloonCachePlugin\Interfaces\CacheDriver;
use Sammyjo20\SaloonCachePlugin\Tests\Fixtures\Connectors\TestConnector;
use Sammyjo20\SaloonCachePlugin\Traits\AlwaysCachesResponse;

class CachedUserRequest extends SaloonRequest
{
    use AlwaysCachesResponse;

    protected ?string $connector = TestConnector::class;

    protected ?string $method = Saloon::GET;

    public function defineEndpoint(): string
    {
        return '/user';
    }

    protected function cacheDriver(): CacheDriver
    {
        return new FlysystemDriver(new Filesystem(new LocalFilesystemAdapter(cachePath())));
    }

    protected function cacheTTLInSeconds(): int
    {
        return 86400;
    }
}
