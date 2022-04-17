<?php

namespace Sammyjo20\SaloonCachePlugin\Drivers;

use League\Flysystem\Filesystem;
use League\Flysystem\UnableToReadFile;
use Sammyjo20\SaloonCachePlugin\Http\CachedResponse;
use Sammyjo20\SaloonCachePlugin\Interfaces\CacheDriver;

class FlysystemDriver implements CacheDriver
{
    /**
     * @param Filesystem $filesystem
     */
    public function __construct(
        protected Filesystem $filesystem,
    ) {
        //
    }

    public function set(string $cacheKey, CachedResponse $response): void
    {
        $this->filesystem->write($cacheKey, serialize($response));
    }

    public function get(string $cacheKey): ?CachedResponse
    {
        try {
            $file = $this->filesystem->read($cacheKey);
        } catch (UnableToReadFile $exception) {
            return null;
        }

        return unserialize($file);
    }
}
