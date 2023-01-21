<?php

declare(strict_types=1);

namespace Saloon\CachePlugin\Tests\Fixtures\Requests;

use League\Flysystem\Filesystem;
use Sammyjo20\Saloon\Constants\Saloon;
use Saloon\CachePlugin\Contracts\Driver;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Saloon\CachePlugin\Drivers\FlysystemDriver;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Saloon\CachePlugin\Traits\AlwaysCacheResponses;
use Saloon\CachePlugin\Tests\Fixtures\Connectors\CachedConnector;

class CachedUserRequestOnCachedConnector extends SaloonRequest
{
    use AlwaysCacheResponses;

    protected ?string $connector = CachedConnector::class;

    protected ?string $method = Saloon::GET;

    public function defineEndpoint(): string
    {
        return '/user';
    }

    public function cacheDriver(): Driver
    {
        return new FlysystemDriver(new Filesystem(new LocalFilesystemAdapter(cachePath() . '/custom')));
    }

    public function cacheTTLInSeconds(): int
    {
        return 30;
    }
}
