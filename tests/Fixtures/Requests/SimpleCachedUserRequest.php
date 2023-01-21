<?php

declare(strict_types=1);

namespace Saloon\CachePlugin\Tests\Fixtures\Requests;

use Sammyjo20\Saloon\Constants\Saloon;
use Saloon\CachePlugin\Contracts\Driver;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Saloon\CachePlugin\Drivers\PsrCacheDriver;
use Saloon\CachePlugin\Traits\AlwaysCacheResponses;
use Saloon\CachePlugin\Tests\Fixtures\Stores\ArrayCache;
use Saloon\CachePlugin\Tests\Fixtures\Connectors\TestConnector;

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
        return new PsrCacheDriver($this->cache);
    }

    public function cacheTTLInSeconds(): int
    {
        return 60;
    }
}
