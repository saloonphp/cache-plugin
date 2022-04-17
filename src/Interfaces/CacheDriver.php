<?php

namespace Sammyjo20\SaloonCachePlugin\Interfaces;

use Sammyjo20\Saloon\Http\SaloonResponse;
use Sammyjo20\SaloonCachePlugin\Http\CachedResponse;

interface CacheDriver
{
    /**
     * Store the cached response on the driver.
     *
     * @param string $cacheKey
     * @param CachedResponse $response
     * @return void
     */
    public function set(string $cacheKey, CachedResponse $response): void;

    /**
     * Get the cached response from the driver.
     *
     * @param string $cacheKey
     * @return CachedResponse
     */
    public function get(string $cacheKey): ?CachedResponse;
}
