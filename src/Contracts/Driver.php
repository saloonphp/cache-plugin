<?php

namespace Saloon\CachePlugin\Contracts;

use Saloon\CachePlugin\Data\CachedResponse;
use Saloon\Data\RecordedResponse;

interface Driver
{
    /**
     * Store the cached response on the driver.
     *
     * @param string $cacheKey
     * @param RecordedResponse $response
     * @return void
     */
    public function set(string $cacheKey, RecordedResponse $response): void;

    /**
     * Get the cached response from the driver.
     *
     * @param string $cacheKey
     * @return RecordedResponse|null
     */
    public function get(string $cacheKey): ?RecordedResponse;

    /**
     * Delete the cached response.
     *
     * @param string $cacheKey
     * @return void
     */
    public function delete(string $cacheKey): void;
}
