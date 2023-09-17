<?php

declare(strict_types=1);

namespace Saloon\CachePlugin\Drivers;

use Saloon\CachePlugin\Contracts\Driver;
use Illuminate\Contracts\Cache\Repository;
use Saloon\CachePlugin\Data\CachedResponse;

class LaravelCacheDriver implements Driver
{
    /**
     * Constructor
     */
    public function __construct(
        protected Repository $store,
    ) {
        //
    }

    /**
     * Store the cached response on the driver.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function set(string $key, CachedResponse $cachedResponse): void
    {
        $this->store->set($key, serialize($cachedResponse), $cachedResponse->ttl);
    }

    /**
     * Get the cached response from the driver.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function get(string $cacheKey): ?CachedResponse
    {
        $data = $this->store->get($cacheKey);

        if (empty($data)) {
            return null;
        }

        return unserialize($data, ['allowed_classes' => true]);
    }

    /**
     * Delete the cached response.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function delete(string $cacheKey): void
    {
        $this->store->delete($cacheKey);
    }
}
