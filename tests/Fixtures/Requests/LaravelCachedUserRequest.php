<?php

namespace Sammyjo20\SaloonCachePlugin\Tests\Fixtures\Requests;

use Illuminate\Support\Facades\Cache;
use Sammyjo20\Saloon\Constants\Saloon;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\SaloonCachePlugin\Drivers\LaravelCacheDriver;
use Sammyjo20\SaloonCachePlugin\Interfaces\DriverInterface;
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

    public function cacheDriver(): DriverInterface
    {
        return new LaravelCacheDriver(Cache::store('file'));
    }

    public function cacheTTLInSeconds(): int
    {
        return 60;
    }
}
