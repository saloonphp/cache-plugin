<?php

namespace Saloon\CachePlugin\Http\Middleware;

use Saloon\CachePlugin\Contracts\Driver;
use Saloon\CachePlugin\Data\CachedResponse;
use Saloon\Contracts\Response;
use Saloon\Contracts\ResponseMiddleware;
use Saloon\Helpers\Date;
use Saloon\Helpers\ResponseRecorder;

class CacheRecorderMiddleware implements ResponseMiddleware
{
    /**
     * Constructor
     *
     * @param \Saloon\CachePlugin\Contracts\Driver $driver
     * @param int $ttl
     * @param string $cacheKey
     */
    public function __construct(
        protected Driver $driver,
        protected int $ttl,
        protected string $cacheKey,
    )
    {
        //
    }

    /**
     * Register a response middleware
     *
     * @param \Saloon\Contracts\Response $response
     * @return void
     */
    public function __invoke(Response $response): void
    {
        if ($response->failed()) {
            return;
        }

        $expiresAt = Date::now()->addSeconds($this->ttl)->toDateTime();

        $this->driver->set(
            key: $this->cacheKey,
            cachedCachedCachedResponse: new CachedResponse(ResponseRecorder::record($response), $expiresAt)
        );
    }
}
