<?php

declare(strict_types=1);

namespace Saloon\CachePlugin\Tests\Fixtures\Requests;

use DateTimeImmutable;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\CachePlugin\Contracts\Driver;
use Saloon\CachePlugin\Traits\HasCaching;
use Saloon\CachePlugin\Contracts\Cacheable;
use Saloon\CachePlugin\Drivers\PsrCacheDriver;
use Saloon\CachePlugin\Tests\Fixtures\Stores\ArrayCache;

class ResolveCacheExpiryCachedRequest extends Request implements Cacheable
{
    use HasCaching;

    /**
     * Method
     */
    protected Method $method = Method::GET;

    public function __construct(
        protected ArrayCache $cache,
        protected int|DateTimeImmutable $cacheExpiry,
    ) {
        //
    }

    /**
     * Resolve the API endpoint
     */
    public function resolveEndpoint(): string
    {
        return '/user';
    }

    /**
     * Resolve the cache driver
     */
    public function resolveCacheDriver(): Driver
    {
        return new PsrCacheDriver($this->cache);
    }

    /**
     * Define the cache expiry in DateTimeImmutable
     */
    public function resolveCacheExpiry(Response $response): int|DateTimeImmutable
    {
        return $this->cacheExpiry;
    }
}
