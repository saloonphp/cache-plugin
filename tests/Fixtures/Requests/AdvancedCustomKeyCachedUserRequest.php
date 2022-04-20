<?php

namespace Sammyjo20\SaloonCachePlugin\Tests\Fixtures\Requests;

use League\Flysystem\Filesystem;
use Sammyjo20\Saloon\Constants\Saloon;
use Sammyjo20\Saloon\Http\SaloonRequest;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Sammyjo20\SaloonCachePlugin\Drivers\FlysystemDriver;
use Sammyjo20\SaloonCachePlugin\Interfaces\DriverInterface;
use Sammyjo20\SaloonCachePlugin\Traits\AlwaysCacheResponses;
use Sammyjo20\SaloonCachePlugin\Tests\Fixtures\Connectors\TestConnector;

class AdvancedCustomKeyCachedUserRequest extends SaloonRequest
{
    use AlwaysCacheResponses;

    protected ?string $connector = TestConnector::class;

    protected ?string $method = Saloon::GET;

    public function defineEndpoint(): string
    {
        return '/user';
    }

    public function cacheDriver(): DriverInterface
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
