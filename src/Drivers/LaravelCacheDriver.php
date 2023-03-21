<?php

namespace Sammyjo20\SaloonCachePlugin\Drivers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Cache\Repository;
use Sammyjo20\SaloonCachePlugin\Data\CachedResponse;
use Sammyjo20\SaloonCachePlugin\Interfaces\DriverInterface;

class LaravelCacheDriver implements DriverInterface
{
    /**
     * @param Repository $store
     */
    public function __construct(
        protected ?Repository $store = null,
    ) {
        $this->store = $this->store ?: Cache::store();
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
