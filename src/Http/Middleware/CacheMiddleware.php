<?php

namespace Saloon\CachePlugin\Http\Middleware;

use Saloon\CachePlugin\Contracts\Driver;
use Saloon\CachePlugin\Data\CachedResponse;
use Saloon\CachePlugin\Helpers\CacheKeyHelper;
use Saloon\Contracts\PendingRequest;
use Saloon\Contracts\RequestMiddleware;
use Saloon\Contracts\SimulatedResponsePayload;

class CacheMiddleware implements RequestMiddleware
{
    /**
     * Constructor
     *
     * @param \Saloon\CachePlugin\Contracts\Driver $driver
     * @param int $ttl
     * @param string|null $cacheKey
     */
    public function __construct(
        protected Driver  $driver,
        protected int     $ttl,
        protected ?string $cacheKey,
    )
    {
        //
    }

    /**
     * Handle the middleware
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @return \Saloon\Contracts\SimulatedResponsePayload|null
     * @throws \JsonException
     */
    public function __invoke(PendingRequest $pendingRequest): ?SimulatedResponsePayload
    {
        $driver = $this->driver;
        $cacheKey = hash('sha256', $this->cacheKey ?? CacheKeyHelper::create($pendingRequest));

        $cachedResponse = $driver->get($cacheKey);

        // If we have found a cached response on the driver, then we will
        // check if the cached response hasn't expired.

        if ($cachedResponse instanceof CachedResponse) {
            // If the cached response is still active, we will return
            // the SimulatedResponsePayload here.

            if ($cachedResponse->hasNotExpired()) {
                return $cachedResponse->getSimulatedResponsePayload();
            }

            // However if it has expired we will delete it and register
            // the CacheRecorderMiddleware.

            $driver->delete($cacheKey);
        }

        // Register the CacheRecorderMiddleware which will record the response
        // and store it on the cache driver for next time. We'll also use
        // the prepend option, so it runs first.

        $pendingRequest->middleware()->onResponse(
            closure: new CacheRecorderMiddleware($driver, $this->ttl, $cacheKey),
            prepend: true,
        );

        return null;
    }
}