<?php

namespace Saloon\CachePlugin\Drivers;

use League\Flysystem\Filesystem;
use League\Flysystem\UnableToReadFile;
use Saloon\CachePlugin\Contracts\Driver;
use Saloon\CachePlugin\Data\CachedResponse;
use Saloon\Data\RecordedResponse;

class FlysystemDriver implements Driver
{
    /**
     * Constructor
     *
     * @param \League\Flysystem\Filesystem $store
     */
    public function __construct(
        protected Filesystem $store,
    ) {
        //
    }

    /**
     * Store the response
     *
     * @param string $key
     * @param \Saloon\CachePlugin\Data\CachedResponse $cachedCachedCachedResponse
     * @return void
     * @throws \League\Flysystem\FilesystemException
     */
    public function set(string $key, CachedResponse $cachedCachedCachedResponse): void
    {
        $this->store->write($key, serialize($cachedCachedCachedResponse));
    }

    /**
     * Retrieve the recorded response
     *
     * @param string $cacheKey
     * @return \Saloon\CachePlugin\Data\CachedResponse|null
     * @throws \League\Flysystem\FilesystemException
     */
    public function get(string $cacheKey): ?CachedResponse
    {
        try {
            $data = $this->store->read($cacheKey);
        } catch (UnableToReadFile $exception) {
            return null;
        }

        if (empty($data)) {
            return null;
        }

        return unserialize($data, ['allowed_classes' => true]);
    }

    /**
     * Delete the cached response
     *
     * @param string $cacheKey
     * @return void
     * @throws \League\Flysystem\FilesystemException
     */
    public function delete(string $cacheKey): void
    {
        try {
            $this->store->delete($cacheKey);
        } catch (UnableToReadFile $exception) {
            //
        }
    }
}
