<?php

namespace Sammyjo20\SaloonCachePlugin\Tests\Fixtures\Connectors;

use Sammyjo20\Saloon\Http\SaloonConnector;

class TestConnector extends SaloonConnector
{
    public function defineBaseUrl(): string
    {
        return testApi();
    }
}
