<?php

namespace Sammyjo20\SaloonCachePlugin\Tests\Fixtures\Requests;

use Illuminate\Support\Facades\Cache;
use League\Flysystem\Filesystem;
use Sammyjo20\Saloon\Constants\Saloon;
use Sammyjo20\Saloon\Http\SaloonRequest;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Sammyjo20\SaloonCachePlugin\Drivers\LaravelCacheDriver;
use Sammyjo20\SaloonCachePlugin\Interfaces\CacheDriver;
use Sammyjo20\SaloonCachePlugin\Drivers\FlysystemDriver;
use Sammyjo20\SaloonCachePlugin\Traits\AlwaysCacheResponses;
use Sammyjo20\SaloonCachePlugin\Tests\Fixtures\Connectors\TestConnector;

class LaravelCachedUserRequest extends SaloonRequest
{
    use AlwaysCacheResponses;

    protected ?string $connector = TestConnector::class;

    protected ?string $method = Saloon::GET;

    public function defineEndpoint(): string
    {
        return '/user';
    }

    public function cacheDriver(): CacheDriver
    {
        return new LaravelCacheDriver(Cache::store('file'));
    }

    public function cacheTTLInSeconds(): int
    {
        return 60;
    }
}
