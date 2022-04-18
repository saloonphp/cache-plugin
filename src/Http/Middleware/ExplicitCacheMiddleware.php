<?php

namespace Sammyjo20\SaloonCachePlugin\Http\Middleware;

use Carbon\CarbonImmutable;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\SaloonCachePlugin\Http\CachedResponse;
use Sammyjo20\SaloonCachePlugin\Interfaces\CacheDriver;

class ExplicitCacheMiddleware
{
    protected SaloonRequest $request;

    protected CacheDriver $cacheDriver;

    protected int $cacheTTL;

    /**
     * @param SaloonRequest $request
     */
    public function __construct(SaloonRequest $request)
    {
        $this->request = $request;
        $this->cacheDriver = $request->cacheDriver();
        $this->cacheTTL = $request->cacheTTLInSeconds();
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
            $cacheKey = $this->request->generateCacheKey($this->request, $request->getHeaders());

            // Check if the cached file exists from the cache key
            // and also check if it hasn't expired.

            $cacheFile = $this->cacheDriver->get($cacheKey);

            // If the file is valid, then we should return the promise here.

            if (isset($cacheFile)) {
                if ($cacheFile->isValid()) {
                    return new FulfilledPromise($cacheFile->getResponse()->withHeader('X-Saloon-Cache', 'Cached'));
                }

                $this->cacheDriver->delete($cacheKey);
            }

            // Otherwise, continue with the request and then cache the response
            // if the status is a 2xx status code.

            $promise = $handler($request, $options);

            return $promise->then(function (ResponseInterface $response) use ($cacheKey) {
                $this->cacheResponse($cacheKey, $response);

                return $response;
            });
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
     * @param string $cacheKey
     * @param ResponseInterface $response
     * @return void
     */
    private function cacheResponse(string $cacheKey, ResponseInterface $response): void
    {
        $status = $response->getStatusCode();

        if ($status < 200 || $status > 300) {
            return;
        }

        $cachedResponse = new CachedResponse($this->generateExpiry(), $response);

        $this->cacheDriver->set($cacheKey, $cachedResponse);
    }
}
