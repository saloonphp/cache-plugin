<?php

namespace Sammyjo20\SaloonCachePlugin\Traits;

use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\SaloonResponse;
use Sammyjo20\SaloonCachePlugin\Interfaces\CacheDriver;
use Sammyjo20\SaloonCachePlugin\Http\Middleware\ExplicitCacheMiddleware;

trait AlwaysCachesResponse
{
    /**
     * @param SaloonRequest $request
     * @return void
     * @throws \JsonException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    public function bootAlwaysCachesResponse(SaloonRequest $request): void
    {
        $driver = $this->cacheDriver();
        $key = $this->generateCacheKey($request);
        $ttl = $this->cacheTTLInSeconds();

        // Run the custom cache middleware.

        $this->addHandler('saloonCache', new ExplicitCacheMiddleware($driver, $key, $ttl));

        // We should also intercept the response and set the "cached" property to true.

        $this->addResponseInterceptor(function (SaloonRequest $request, SaloonResponse $response) {
            $isCached = $response->header('X-Saloon-Cache') === 'Cached';

            if ($isCached) {
                $response->setCached(true);
            }

            return $response;
        });
    }

    /**
     * Customise the cache key used.
     *
     * @param SaloonRequest $request
     * @return string
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     * @throws \JsonException
     */
    protected function cacheKey(SaloonRequest $request): string
    {
        $requestUrl = $request->getFullRequestUrl();
        $className = get_class($request);
        $headers = $request->getHeaders();
        $config = $request->getConfig();

        return json_encode(compact('requestUrl', 'className', 'headers', 'config'), JSON_THROW_ON_ERROR);
    }

    /**
     * @param SaloonRequest $request
     * @return string
     * @throws \JsonException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    private function generateCacheKey(SaloonRequest $request): string
    {
        return hash('sha256', $this->cacheKey($request));
    }

    /**
     * Return an instance of the cache driver that should be used.
     *
     * @return mixed
     */
    abstract protected function cacheDriver(): CacheDriver;

    /**
     * Define the cache TTL (Time-to-live) in seconds.
     *
     * @return int
     */
    abstract protected function cacheTTLInSeconds(): int;
}
