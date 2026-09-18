<?php

declare(strict_types=1);

namespace Saloon\CachePlugin\Http\Middleware;

use DateTimeImmutable;
use Saloon\Http\Response;
use Saloon\Data\RecordedResponse;
use Saloon\CachePlugin\Contracts\Driver;
use Saloon\Contracts\ResponseMiddleware;
use Saloon\CachePlugin\Contracts\Cacheable;
use Saloon\CachePlugin\Data\CachedResponse;
use Saloon\CachePlugin\Exceptions\HasCachingException;

class CacheRecorderMiddleware implements ResponseMiddleware
{
    /**
     * Constructor
     */
    public function __construct(
        protected Driver $driver,
        protected string $cacheKey,
    ) {
        //
    }

    /**
     * Register a response middleware
     *
     * @throws \Exception
     */
    public function __invoke(Response $response): void
    {
        if ($response->failed()) {
            return;
        }

        $request = $response->getRequest();
        $connector = $response->getConnector();

        if (! $request instanceof Cacheable && ! $connector instanceof Cacheable) {
            throw new HasCachingException(sprintf('Your connector or request must implement %s to use the HasCaching plugin', Cacheable::class));
        }

        $expiresAt = $request instanceof Cacheable
            ? $request->resolveCacheExpiry($response)
            : $connector->resolveCacheExpiry($response);

        if (is_int($expiresAt)) {
            $expiresAt = new DateTimeImmutable('+' . $expiresAt .' seconds');
        }

        $this->driver->set(
            key: $this->cacheKey,
            cachedResponse: new CachedResponse(RecordedResponse::fromResponse($response), $expiresAt)
        );
    }
}
