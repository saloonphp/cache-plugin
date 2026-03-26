<?php

declare(strict_types=1);

namespace Saloon\CachePlugin\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use League\Flysystem\Filesystem;
use Saloon\CachePlugin\Contracts\Driver;
use Saloon\CachePlugin\Traits\HasCaching;
use Saloon\CachePlugin\Contracts\Cacheable;
use Saloon\CachePlugin\Drivers\FlysystemDriver;
use League\Flysystem\Local\LocalFilesystemAdapter;

class ResponseBasedExpiryRequest extends Request implements Cacheable
{
    use HasCaching;

    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/user';
    }

    public function resolveCacheDriver(): Driver
    {
        return new FlysystemDriver(new Filesystem(new LocalFilesystemAdapter(cachePath())));
    }

    public function resolveCacheExpiry(Response $response): int
    {
        return $response->json()['expiry'];
    }
}
