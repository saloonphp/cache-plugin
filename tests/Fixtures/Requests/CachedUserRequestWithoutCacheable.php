<?php

declare(strict_types=1);

namespace Saloon\CachePlugin\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use League\Flysystem\Filesystem;
use Saloon\CachePlugin\Contracts\Driver;
use Saloon\CachePlugin\Traits\HasCaching;
use Saloon\CachePlugin\Drivers\FlysystemDriver;
use League\Flysystem\Local\LocalFilesystemAdapter;

class CachedUserRequestWithoutCacheable extends Request
{
    use HasCaching;

    /**
     * Method
     */
    protected Method $method = Method::GET;

    /**
     * Resolve the API endpoint
     */
    public function resolveEndpoint(): string
    {
        return '/user';
    }

    /**
     * Resolve the cache driver
     */
    public function resolveCacheDriver(): Driver
    {
        return new FlysystemDriver(new Filesystem(new LocalFilesystemAdapter(cachePath())));
    }

    /**
     * Define the cache expiry in seconds
     */
    public function cacheExpiryInSeconds(): int
    {
        return 60;
    }
}
