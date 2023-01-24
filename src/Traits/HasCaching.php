<?php

declare(strict_types=1);

namespace Saloon\CachePlugin\Traits;

use Saloon\Enums\Method;
use Saloon\Contracts\PendingRequest;
use Saloon\CachePlugin\Contracts\Cacheable;
use Saloon\CachePlugin\Exceptions\HasCachingException;
use Saloon\CachePlugin\Http\Middleware\CacheMiddleware;

trait HasCaching
{
    /**
     * Is caching enabled?
     *
     * @var bool
     */
    protected bool $cachingEnabled = true;

    /**
     * Should the existing cache be invalidated?
     *
     * @var bool
     */
    protected bool $invalidateCache = false;

    /**
     * Boot the "HasCaching" plugin
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @return void
     * @throws \Saloon\CachePlugin\Exceptions\HasCachingException
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

        // Register a request middleware which wil handle the caching and recording
        // of real responses for caching.

        $pendingRequest->middleware()->onRequest(
            callable: new CacheMiddleware($cacheDriver, $cacheExpiryInSeconds, $this->cacheKey($pendingRequest), $this->invalidateCache),
        );
    }

    /**
     * Define a custom cache key
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @return string|null
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
