<?php

declare(strict_types=1);

namespace Saloon\CachePlugin\Drivers;

use Psr\SimpleCache\CacheInterface;
use Saloon\CachePlugin\Contracts\Driver;
use Saloon\CachePlugin\Data\CachedResponse;

/**
 * PSR-16 Cache Driver
 */
class PsrCacheDriver implements Driver
{
    /**
     * Constructor
     */
    public function __construct(
        protected CacheInterface $store,
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
        $data = $this->store->get($cacheKey, null);

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
