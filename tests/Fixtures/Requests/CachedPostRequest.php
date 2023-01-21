<?php

declare(strict_types=1);

namespace Saloon\CachePlugin\Tests\Fixtures\Requests;

use League\Flysystem\Filesystem;
use Saloon\CachePlugin\Contracts\Cacheable;
use Saloon\CachePlugin\Traits\HasCaching;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;
use Saloon\CachePlugin\Contracts\Driver;
use Saloon\CachePlugin\Drivers\FlysystemDriver;
use League\Flysystem\Local\LocalFilesystemAdapter;

class CachedPostRequest extends Request implements Cacheable, HasBody
{
    use HasCaching;
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/data';
    }

    public function defaultData(): array
    {
        return [
            'name' => 'Sammy',
        ];
    }

    public function resolveCacheDriver(): Driver
    {
        return new FlysystemDriver(new Filesystem(new LocalFilesystemAdapter(cachePath())));
    }

    public function cacheExpiryInSeconds(): int
    {
        return 60;
    }
}
