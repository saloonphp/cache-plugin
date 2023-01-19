<?php

namespace Saloon\CachePlugin\Drivers;

use League\Flysystem\Filesystem;
use League\Flysystem\UnableToReadFile;
use Saloon\CachePlugin\Contracts\Driver;
use Saloon\CachePlugin\Data\CachedResponse;

class FlysystemDriver implements Driver
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
