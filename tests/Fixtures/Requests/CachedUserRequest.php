<?php

declare(strict_types=1);

namespace Saloon\CachePlugin\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use League\Flysystem\Filesystem;
use Saloon\CachePlugin\Contracts\Driver;
use Saloon\CachePlugin\Traits\HasCaching;
use Saloon\CachePlugin\Contracts\Cacheable;
use Saloon\CachePlugin\Drivers\FlysystemDriver;
use League\Flysystem\Local\LocalFilesystemAdapter;

class CachedUserRequest extends Request implements Cacheable
{
    use HasCaching;

    /**
     * Method
     *
     * @var \Saloon\Enums\Method
     */
    protected Method $method = Method::GET;

    /**
     * Resolve the API endpoint
     *
     * @return string
     */
    public function resolveEndpoint(): string
    {
        return '/user';
    }

    /**
     * Resolve the cache driver
     *
     * @return \Saloon\CachePlugin\Contracts\Driver
     */
    public function resolveCacheDriver(): Driver
    {
        return new FlysystemDriver(new Filesystem(new LocalFilesystemAdapter(cachePath())));
    }

    /**
     * Define the cache expiry in seconds
     *
     * @return int
     */
    public function cacheExpiryInSeconds(): int
    {
        return 60;
    }
}
