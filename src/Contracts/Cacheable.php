<?php

declare(strict_types=1);

namespace Saloon\CachePlugin\Contracts;

interface Cacheable
{
    /**
     * Resolve the driver responsible for caching
     */
    public function resolveCacheDriver(): Driver;

    /**
     * Define the cache expiry in seconds
     */
    public function cacheExpiryInSeconds(): int;
}
