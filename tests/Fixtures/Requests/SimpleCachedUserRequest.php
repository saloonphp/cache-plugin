<?php

namespace Saloon\CachePlugin\Tests\Fixtures\Requests;

use Saloon\CachePlugin\Contracts\Driver;
use Saloon\CachePlugin\Drivers\SimpleCacheDriver;
use Saloon\CachePlugin\Tests\Fixtures\Connectors\TestConnector;
use Saloon\CachePlugin\Tests\Fixtures\Stores\ArrayCache;
use Saloon\CachePlugin\Traits\AlwaysCacheResponses;
use Sammyjo20\Saloon\Constants\Saloon;
use Sammyjo20\Saloon\Http\SaloonRequest;

class SimpleCachedUserRequest extends SaloonRequest
{
    use AlwaysCacheResponses;

    protected ?string $connector = TestConnector::class;

    protected ?string $method = Saloon::GET;

    public function defineEndpoint(): string
    {
        return '/user';
    }

    public function __construct(protected ArrayCache $cache)
    {
        //
    }

    public function cacheDriver(): Driver
    {
        return new SimpleCacheDriver($this->cache);
    }

    public function cacheTTLInSeconds(): int
    {
        return 60;
    }
}
