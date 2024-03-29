<?php

declare(strict_types=1);

namespace Saloon\CachePlugin\Http\Middleware;

use Saloon\Http\Response;
use Saloon\Enums\PipeOrder;
use Saloon\Http\PendingRequest;
use Saloon\Contracts\FakeResponse;
use Saloon\Contracts\RequestMiddleware;
use Saloon\CachePlugin\Contracts\Driver;
use Saloon\CachePlugin\Data\CachedResponse;
use Saloon\CachePlugin\Helpers\CacheKeyHelper;

class CacheMiddleware implements RequestMiddleware
{
    /**
     * Constructor
     */
    public function __construct(
        protected Driver  $driver,
        protected int     $ttl,
        protected ?string $cacheKey,
        protected bool    $invalidate = false,
    ) {
        //
    }

    /**
     * Handle the middleware
     *
     * @throws \JsonException
     */
    public function __invoke(PendingRequest $pendingRequest): ?FakeResponse
    {
        $driver = $this->driver;
        $cacheKey = hash('sha256', $this->cacheKey ?? CacheKeyHelper::create($pendingRequest));

        $cachedResponse = $driver->get($cacheKey);

        // If we have found a cached response on the driver, then we will
        // check if the cached response hasn't expired.

        if ($cachedResponse instanceof CachedResponse) {
            // If the cached response is still active, we will return
            // the SimulatedResponsePayload here.

            if ($this->invalidate === false && $cachedResponse->hasNotExpired()) {
                $pendingRequest->middleware()->onResponse(fn (Response $response) => $response->setCached(true), order: PipeOrder::FIRST);

                return $cachedResponse->getFakeResponse();
            }

            // However if it has expired we will delete it and register
            // the CacheRecorderMiddleware.

            $driver->delete($cacheKey);
        }

        // Register the CacheRecorderMiddleware which will record the response
        // and store it on the cache driver for next time. We'll also use
        // the prepend option, so it runs first.

        $pendingRequest->middleware()->onResponse(
            callable: new CacheRecorderMiddleware($driver, $this->ttl, $cacheKey),
            order: PipeOrder::FIRST
        );

        return null;
    }
}
