<?php

namespace Saloon\CachePlugin\Traits;

use Saloon\CachePlugin\Contracts\Cacheable;
use Saloon\CachePlugin\Exceptions\HasCachingException;
use Saloon\CachePlugin\Http\Middleware\CacheMiddleware;
use Saloon\Contracts\PendingRequest;
use Saloon\Enums\Method;

trait HasCaching
{
    /**
     * Boot the "HasCaching" plugin
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @return void
     * @throws \Saloon\CachePlugin\Exceptions\HasCachingException
     */
    public function bootHasCaching(PendingRequest $pendingRequest): void
    {
        if (! in_array($pendingRequest->getMethod(), [Method::GET, Method::OPTIONS], true)) {
            return;
        }

        $request = $pendingRequest->getRequest();
        $connector = $pendingRequest->getConnector();

        if (! $request instanceof Cacheable && ! $connector instanceof Cacheable) {
            throw new HasCachingException(sprintf('Your connector or request must implement %s to use the HasCaching plugin', Cacheable::class));
        }

        $cacheDriver = $request instanceof Cacheable
            ? $request->resolveCacheDriver()
            : $connector->resolveCacheDriver();

        $cacheExpiryInSeconds = $request instanceof Cacheable
            ? $request->cacheExpiryInSeconds()
            : $connector->cacheExpiryInSeconds();

        // Register a request middleware which wil handle the caching and recording
        // of real responses for caching.

        $pendingRequest->middleware()->onRequest(
            closure: new CacheMiddleware($cacheDriver, $cacheExpiryInSeconds, $this->cacheKey()),
        );
    }

    /**
     * Define a custom cache key
     *
     * @return string|null
     */
    protected function cacheKey(): ?string
    {
        return null;
    }
}
