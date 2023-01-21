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
use Saloon\CachePlugin\Tests\Fixtures\Connectors\TestConnector;

class AdvancedCustomKeyCachedUserRequest extends SaloonRequest
{
    use AlwaysCacheResponses;

    protected ?string $connector = TestConnector::class;

    protected ?string $method = Saloon::GET;

    public function defineEndpoint(): string
    {
        return '/user';
    }

    public function cacheDriver(): Driver
    {
        return new FlysystemDriver(new Filesystem(new LocalFilesystemAdapter(cachePath())));
    }

    public function cacheTTLInSeconds(): int
    {
        return 60;
    }

    protected function cacheKey(SaloonRequest $request, array $headers, bool $hashKey = true): string
    {
        return 'Howdy!';
    }

    public function generateCacheKey(SaloonRequest $request, array $headers): string
    {
        return $this->cacheKey($request, $headers);
    }
}
