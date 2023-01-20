<?php

namespace Saloon\CachePlugin\Contracts;

use Saloon\CachePlugin\Data\CachedResponse;
use Saloon\Data\RecordedResponse;

interface Driver
{
    /**
     * Store the cached response on the driver.
     *
     * @param string $key
     * @param CachedResponse $cachedResponse
     * @return void
     */
    public function set(string $key, CachedResponse $cachedResponse): void;

    /**
     * Get the cached response from the driver.
     *
     * @param string $cacheKey
     * @return RecordedResponse|null
     */
    public function get(string $cacheKey): ?CachedResponse;

    /**
     * Delete the cached response.
     *
     * @param string $cacheKey
     * @return void
     */
    public function delete(string $cacheKey): void;
}