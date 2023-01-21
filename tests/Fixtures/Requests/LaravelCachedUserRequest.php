<?php

declare(strict_types=1);

namespace Saloon\CachePlugin\Tests\Fixtures\Requests;

use Illuminate\Support\Facades\Cache;
use Sammyjo20\Saloon\Constants\Saloon;
use Saloon\CachePlugin\Contracts\Driver;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Saloon\CachePlugin\Drivers\LaravelCacheDriver;
use Saloon\CachePlugin\Traits\AlwaysCacheResponses;
use Saloon\CachePlugin\Tests\Fixtures\Connectors\TestConnector;

class LaravelCachedUserRequest extends SaloonRequest
{
    use AlwaysCacheResponses;

    protected ?string $connector = TestConnector::class;

    protected ?string $method = Saloon::GET;

    public function defineEndpoint(): string
    {
        return '/user';
    }

    public function cacheDriver(): Driver
    {
        return new LaravelCacheDriver(Cache::store('file'));
    }

    public function cacheTTLInSeconds(): int
    {
        return 60;
    }
}
