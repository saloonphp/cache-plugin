<?php

namespace Saloon\CachePlugin\Drivers;

use Illuminate\Contracts\Cache\Repository;
use Saloon\CachePlugin\Contracts\Driver;
use Saloon\CachePlugin\Data\CachedResponse;

class LaravelCacheDriver implements Driver
{
    /**
     * @param Repository $store
     */
    public function __construct(
        protected Repository $store,
    ) {
        //
    }

    /**
     * @param string $key
     * @param \Saloon\CachePlugin\Data\CachedResponse $cachedResponse
     * @return void
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function set(string $key, CachedResponse $cachedResponse): void
    {
        // Todo: work out diff in seconds

        $this->store->set($key, serialize($cachedResponse), $cachedResponse->expiresAt->diffInSeconds());
    }

    /**
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
     * @param string $cacheKey
     * @return void
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function delete(string $cacheKey): void
    {
        $this->store->delete($cacheKey);
    }
}
