<?php

declare(strict_types=1);

namespace Saloon\CachePlugin\Traits;

use Saloon\Enums\Method;
use Saloon\Enums\PipeOrder;
use Saloon\Http\PendingRequest;
use Saloon\CachePlugin\Contracts\Cacheable;
use Saloon\CachePlugin\Exceptions\HasCachingException;
use Saloon\CachePlugin\Http\Middleware\CacheMiddleware;

trait HasCaching
{
    /**
     * Is caching enabled?
     */
    protected bool $cachingEnabled = true;

    /**
     * Should the existing cache be invalidated?
     */
    protected bool $invalidateCache = false;

    /**
     * Boot the "HasCaching" plugin
     *
     * @throws \Saloon\CachePlugin\Exceptions\HasCachingException
     * @throws \Saloon\Exceptions\DuplicatePipeNameException
     */
    public function bootHasCaching(PendingRequest $pendingRequest): void
    {
        $request = $pendingRequest->getRequest();
        $connector = $pendingRequest->getConnector();

        if (! $request instanceof Cacheable && ! $connector instanceof Cacheable) {
            throw new HasCachingException(sprintf('Your connector or request must implement %s to use the HasCaching plugin', Cacheable::class));
        }

        if ($this->cachingEnabled === false) {
            return;
        }

        if (! in_array($pendingRequest->getMethod(), $this->getCacheableMethods(), true)) {
            return;
        }

        $cacheDriver = $request instanceof Cacheable
            ? $request->resolveCacheDriver()
            : $connector->resolveCacheDriver();

        $cacheExpiryInSeconds = $request instanceof Cacheable
            ? $request->cacheExpiryInSeconds()
            : $connector->cacheExpiryInSeconds();

        // Register a request middleware which wil handle the caching
        // and recording of real responses for caching.

        $pendingRequest->middleware()->onRequest(function (PendingRequest $middlewarePendingRequest) use ($cacheDriver, $cacheExpiryInSeconds) {
            // We'll call the cache middleware invokable class with the $middlewarePendingRequest
            // because this $pendingRequest has everything loaded, unlike the instance that
            // the plugin is provided. This allows us to have access to body and merged
            // properties.

            return call_user_func(new CacheMiddleware($cacheDriver, $cacheExpiryInSeconds, $this->cacheKey($middlewarePendingRequest), $this->invalidateCache), $middlewarePendingRequest);
        }, order: PipeOrder::FIRST);
    }

    /**
     * Define a custom cache key
     */
    protected function cacheKey(PendingRequest $pendingRequest): ?string
    {
        return null;
    }

    /**
     * Enable caching for the request.
     *
     * @return $this
     */
    public function enableCaching(): static
    {
        $this->cachingEnabled = true;

        return $this;
    }

    /**
     * Disable caching for the request.
     *
     * @return $this
     */
    public function disableCaching(): static
    {
        $this->cachingEnabled = false;

        return $this;
    }

    /**
     * Invalidate the current cache and refresh the cache.
     *
     * @return $this
     */
    public function invalidateCache(): static
    {
        $this->invalidateCache = true;

        return $this;
    }

    /**
     * Define the cacheable methods that can be used
     *
     * @return array<\Saloon\Enums\Method>
     */
    protected function getCacheableMethods(): array
    {
        return [Method::GET, Method::OPTIONS];
    }
}
