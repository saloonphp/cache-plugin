<?php

declare(strict_types=1);

namespace Saloon\CachePlugin\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\CachePlugin\Contracts\Driver;
use Saloon\CachePlugin\Traits\HasCaching;
use Saloon\CachePlugin\Contracts\Cacheable;
use Saloon\CachePlugin\Drivers\PsrCacheDriver;
use Saloon\CachePlugin\Tests\Fixtures\Stores\ArrayCache;

class PsrCachedUserRequest extends Request implements Cacheable
{
    use HasCaching;

    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/user';
    }

    public function __construct(protected ArrayCache $cache)
    {
        //
    }

    public function resolveCacheDriver(): Driver
    {
        return new PsrCacheDriver($this->cache);
    }

    public function cacheExpiryInSeconds(): int
    {
        return 60;
    }
}
