<?php

declare(strict_types=1);

namespace Saloon\CachePlugin\Tests\Fixtures\Connectors;

use Saloon\Http\Request;
use Saloon\Http\Connector;
use Saloon\CachePlugin\Helpers\CacheKeyHelper;

class TestConnector extends Connector
{
    public function resolveBaseUrl(): string
    {
        return testApi();
    }

    public function getCacheKey(Request $request): string
    {
        return hash('sha256', CacheKeyHelper::create($this->createPendingRequest($request, $this->mockClient)));
    }
}
