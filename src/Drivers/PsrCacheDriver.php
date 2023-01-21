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
     *
     * @param CacheInterface $store
     */
    public function __construct(
        protected CacheInterface $store,
    ) {
        //
    }

    /**
     * Store the cached response on the driver.
     *
     * @param string $key
     * @param \Saloon\CachePlugin\Data\CachedResponse $cachedResponse
     * @return void
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function set(string $key, CachedResponse $cachedResponse): void
    {
        $this->store->set($key, serialize($cachedResponse), $cachedResponse->ttl);
    }

    /**
     * Get the cached response from the driver.
     *
     * @param string $cacheKey
     * @return CachedResponse|null
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
     * @param string $cacheKey
     * @return void
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function delete(string $cacheKey): void
    {
        $this->store->delete($cacheKey);
    }
}
