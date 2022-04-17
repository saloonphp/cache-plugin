<?php

namespace Sammyjo20\SaloonCachePlugin\Http\Middleware;

use Carbon\CarbonImmutable;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use Sammyjo20\SaloonCachePlugin\Http\CachedResponse;
use Sammyjo20\SaloonCachePlugin\Interfaces\CacheDriver;

class ExplicitCacheMiddleware
{
    /**
     * @param CacheDriver $cacheDriver
     * @param string $cacheKey
     * @param int $cacheTTL
     */
    public function __construct(
        protected CacheDriver $cacheDriver,
        protected string      $cacheKey,
        protected int         $cacheTTL,
    ) {
        //
    }

    /**
     * Explicitly cache every response.
     *
     * @param callable $handler
     * @return callable
     */
    public function __invoke(callable $handler): callable
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            // Check if the cached file exists from the cache key
            // and also check if it hasn't expired.

            $cacheFile = $this->cacheDriver->get($this->cacheKey);

            // If the file is valid, then we should return the promise here.

            if (isset($cacheFile) && $cacheFile->isValid()) {
                return new FulfilledPromise($cacheFile->getResponse()->withHeader('X-Saloon-Cache', 'Cached'));
            }

            // Otherwise, continue with the request and then cache the response
            // if the status is a 2xx status code.

            $promise = $handler($request, $options);

            return $promise->then(
                function (ResponseInterface $response) {
                    $this->cacheResponse($response);

                    return $response;
                }
            );
        };
    }

    /**
     * Generate a Carbon immutable instance with the expiry added.
     *
     * @return CarbonImmutable
     */
    private function generateExpiry(): CarbonImmutable
    {
        return CarbonImmutable::now()->addSeconds($this->cacheTTL);
    }

    /**
     * Cache the response. Only cache if the response is a 2xx response.
     *
     * @param ResponseInterface $response
     * @return void
     */
    private function cacheResponse(ResponseInterface $response): void
    {
        $status = $response->getStatusCode();

        if ($status < 200 || $status > 300) {
            return;
        }

        $cachedResponse = new CachedResponse($this->generateExpiry(), $response);

        $this->cacheDriver->set($this->cacheKey, $cachedResponse);
    }
}
