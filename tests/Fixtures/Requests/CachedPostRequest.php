<?php

declare(strict_types=1);

namespace Saloon\CachePlugin\Tests\Fixtures\Requests;

use League\Flysystem\Filesystem;
use Sammyjo20\Saloon\Constants\Saloon;
use Saloon\CachePlugin\Contracts\Driver;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Saloon\CachePlugin\Drivers\FlysystemDriver;
use Sammyjo20\Saloon\Traits\Plugins\HasJsonBody;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Saloon\CachePlugin\Traits\AlwaysCacheResponses;
use Saloon\CachePlugin\Tests\Fixtures\Connectors\TestConnector;

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

    public function cacheDriver(): Driver
    {
        return new FlysystemDriver(new Filesystem(new LocalFilesystemAdapter(cachePath())));
    }

    public function cacheTTLInSeconds(): int
    {
        return 60;
    }
}
