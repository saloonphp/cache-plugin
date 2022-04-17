<?php

namespace Sammyjo20\SaloonCachePlugin\Helpers;

use Sammyjo20\Saloon\Http\SaloonRequest;

class CacheKeyHelper
{
    /**
     * Generate a hash string
     *
     * @param SaloonRequest $request
     * @param bool $generateHash
     * @return string
     * @throws \JsonException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    public static function generateFromRequest(SaloonRequest $request, bool $generateHash = true): string
    {
    }
}
