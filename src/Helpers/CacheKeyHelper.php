<?php

declare(strict_types=1);

namespace Saloon\CachePlugin\Helpers;

use Saloon\Http\PendingRequest;

class CacheKeyHelper
{
    /**
     * Create a cache key from the request class name, URL, query and headers
     *
     * @throws \JsonException
     */
    public static function create(PendingRequest $pendingRequest): string
    {
        $className = $pendingRequest->getRequest()::class;
        $requestUrl = $pendingRequest->getUrl();
        $query = $pendingRequest->query()->all();
        $headers = $pendingRequest->headers()->all();

        return json_encode(compact('className', 'requestUrl', 'query', 'headers'), JSON_THROW_ON_ERROR);
    }
}
