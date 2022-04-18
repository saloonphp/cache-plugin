<?php

namespace Sammyjo20\SaloonCachePlugin\Drivers;

use Illuminate\Cache\Repository;
use Sammyjo20\SaloonCachePlugin\Data\CachedResponse;
use Sammyjo20\SaloonCachePlugin\Interfaces\CacheDriver;

class LaravelCacheDriver implements CacheDriver
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
     * @param string $cacheKey
     * @param CachedResponse $response
     * @return void
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function set(string $cacheKey, CachedResponse $response): void
    {
        $this->store->set($cacheKey, serialize($response), $response->getExpiry()->diffInSeconds());
    }

    /**
     * @param string $cacheKey
     * @return CachedResponse|null
     */
    public function get(string $cacheKey): ?CachedResponse
    {
        $data = $this->store->get($cacheKey, null);

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
