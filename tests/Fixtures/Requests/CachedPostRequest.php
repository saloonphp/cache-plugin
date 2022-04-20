<?php

namespace Sammyjo20\SaloonCachePlugin\Tests\Fixtures\Requests;

use League\Flysystem\Filesystem;
use Sammyjo20\Saloon\Constants\Saloon;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Traits\Plugins\HasJsonBody;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Sammyjo20\SaloonCachePlugin\Interfaces\DriverInterface;
use Sammyjo20\SaloonCachePlugin\Drivers\FlysystemDriver;
use Sammyjo20\SaloonCachePlugin\Traits\AlwaysCacheResponses;
use Sammyjo20\SaloonCachePlugin\Tests\Fixtures\Connectors\TestConnector;

class CachedPostRequest extends SaloonRequest
{
    use AlwaysCacheResponses;
    use HasJsonBody;

    protected ?string $connector = TestConnector::class;

    protected ?string $method = Saloon::POST;

    public function defineEndpoint(): string
    {
        return '/data';
    }

    public function defaultData(): array
    {
        return [
            'name' => 'Sammy',
        ];
    }

    public function cacheDriver(): DriverInterface
    {
        return new FlysystemDriver(new Filesystem(new LocalFilesystemAdapter(cachePath())));
    }

    public function cacheTTLInSeconds(): int
    {
        return 60;
    }
}
