<?php

declare(strict_types=1);

namespace Saloon\CachePlugin\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;

class TestConnector extends Connector
{
    public function resolveBaseUrl(): string
    {
        return testApi();
    }
}
