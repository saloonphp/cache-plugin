<?php

declare(strict_types=1);

namespace Saloon\CachePlugin\Drivers;

use League\Flysystem\Filesystem;
use League\Flysystem\UnableToReadFile;
use Saloon\CachePlugin\Contracts\Driver;
use Saloon\CachePlugin\Data\CachedResponse;

class FlysystemDriver implements Driver
{
    /**
     * Constructor
     */
    public function __construct(
        protected Filesystem $store,
    ) {
        //
    }

    /**
     * Store the cached response on the driver.
     *
     * @throws \League\Flysystem\FilesystemException
     */
    public function set(string $key, CachedResponse $cachedResponse): void
    {
        $this->store->write($key, serialize($cachedResponse));
    }

    /**
     * Get the cached response from the driver.
     *
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
