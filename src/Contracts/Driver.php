<?php

declare(strict_types=1);

namespace Saloon\CachePlugin\Contracts;

use Saloon\Data\RecordedResponse;
use Saloon\CachePlugin\Data\CachedResponse;

interface Driver
{
    /**
     * Store the cached response on the driver.
     */
    public function set(string $key, CachedResponse $cachedResponse): void;

    /**
     * Get the cached response from the driver.
     *
     * @return RecordedResponse|null
     */
    public function get(string $cacheKey): ?CachedResponse;

    /**
     * Delete the cached response.
     */
    public function delete(string $cacheKey): void;
}
