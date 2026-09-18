<?php

declare(strict_types=1);

namespace Saloon\CachePlugin\Traits;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Connector;
use Saloon\Enums\PipeOrder;
use Saloon\Http\PendingRequest;
use Saloon\CachePlugin\Contracts\Cacheable;
use Saloon\CachePlugin\Helpers\CacheKeyHelper;
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
     * Clear the cached response without sending a request.
     *
     * When used on a Request, pass the Connector.
     * When used on a Connector, pass the Request.
     *
     * @throws \JsonException
     * @throws \Saloon\CachePlugin\Exceptions\HasCachingException
     */
    public function clearCache(Connector|Request $counterpart): void
    {
        if ($this instanceof Request && ! $counterpart instanceof Connector) {
            throw new HasCachingException('You must provide a Connector instance when calling clearCache() on a Request.');
        }

        if ($this instanceof Connector && ! $counterpart instanceof Request) {
            throw new HasCachingException('You must provide a Request instance when calling clearCache() on a Connector.');
        }

        $pendingRequest = $this instanceof Request
            ? $counterpart->createPendingRequest($this)
            : $this->createPendingRequest($counterpart);

        $cacheDriver = $pendingRequest->getRequest() instanceof Cacheable
            ? $pendingRequest->getRequest()->resolveCacheDriver()
            : $pendingRequest->getConnector()->resolveCacheDriver();

        $cacheDriver->delete(CacheKeyHelper::createHashed($pendingRequest, $this->cacheKey($pendingRequest)));
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
