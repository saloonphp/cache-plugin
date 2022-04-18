<?php

namespace Sammyjo20\SaloonCachePlugin\Drivers;

use League\Flysystem\Filesystem;
use League\Flysystem\UnableToReadFile;
use Sammyjo20\SaloonCachePlugin\Data\CachedResponse;
use Sammyjo20\SaloonCachePlugin\Interfaces\CacheDriver;

class FlysystemDriver implements CacheDriver
{
    /**
     * @param Filesystem $store
     */
    public function __construct(
        protected Filesystem $store,
    ) {
        //
    }

    /**
     * @param string $cacheKey
     * @param CachedResponse $response
     * @return void
     * @throws \League\Flysystem\FilesystemException
     */
    public function set(string $cacheKey, CachedResponse $response): void
    {
        $this->store->write($cacheKey, serialize($response));
    }

    /**
     * @param string $cacheKey
     * @return CachedResponse|null
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
