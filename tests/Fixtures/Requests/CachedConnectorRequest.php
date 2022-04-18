<?php

namespace Sammyjo20\SaloonCachePlugin\Tests\Fixtures\Requests;

use Sammyjo20\Saloon\Constants\Saloon;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\SaloonCachePlugin\Tests\Fixtures\Connectors\CachedConnector;

class CachedConnectorRequest extends SaloonRequest
{
    protected ?string $connector = CachedConnector::class;

    protected ?string $method = Saloon::GET;

    public function defineEndpoint(): string
    {
        return '/user';
    }
}
