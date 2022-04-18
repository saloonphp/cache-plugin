<?php

namespace Sammyjo20\SaloonCachePlugin\Traits;

use Sammyjo20\Saloon\Constants\Saloon;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\SaloonResponse;
use Sammyjo20\SaloonCachePlugin\Interfaces\CacheDriver;
use Sammyjo20\SaloonCachePlugin\Http\Middleware\ExplicitCacheMiddleware;

trait AlwaysCachesResponses
{
    /**
     * The methods that caching is enabled.
     *
     * @var array
     */
    private array $safeMethods = [
        Saloon::GET,
        Saloon::OPTIONS,
    ];

    /**
     * @param SaloonRequest $request
     * @return void
     * @throws \JsonException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    public function bootAlwaysCachesResponses(SaloonRequest $request): void
    {
        // We should only cache on "read-only" methods and not on methods
        // like POST, PUT.

        if (! in_array($request->getMethod(), $this->safeMethods, false)) {
            return;
        }

        // Run the custom cache middleware.

        $request->addHandler('saloonCache', new ExplicitCacheMiddleware($request));

        // We should also intercept the response and set the "cached" property to true.

        $request->addResponseInterceptor(function (SaloonRequest $request, SaloonResponse $response) {
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
     * @param array $headers
     * @param array $config
     * @return string
     * @throws \JsonException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    protected function cacheKey(SaloonRequest $request, array $headers): string
    {
        $requestUrl = $request->getFullRequestUrl();
        $className = get_class($request);

        return json_encode(compact('requestUrl', 'className', 'headers'), JSON_THROW_ON_ERROR);
    }

    /**
     * @param SaloonRequest $request
     * @param array $headers
     * @return string
     * @throws \JsonException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    public function generateCacheKey(SaloonRequest $request, array $headers): string
    {
        return hash('sha256', $this->cacheKey($request, $headers));
    }

    /**
     * Return an instance of the cache driver that should be used.
     *
     * @return mixed
     */
    abstract public function cacheDriver(): CacheDriver;

    /**
     * Define the cache TTL (Time-to-live) in seconds.
     *
     * @return int
     */
    abstract public function cacheTTLInSeconds(): int;
}
