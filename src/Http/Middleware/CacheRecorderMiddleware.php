<?php

declare(strict_types=1);

namespace Saloon\CachePlugin\Http\Middleware;

use DateTimeImmutable;
use Saloon\Http\Response;
use Saloon\Data\RecordedResponse;
use Saloon\CachePlugin\Contracts\Driver;
use Saloon\Contracts\ResponseMiddleware;
use Saloon\CachePlugin\Data\CachedResponse;

class CacheRecorderMiddleware implements ResponseMiddleware
{
    /**
     * Constructor
     */
    public function __construct(
        protected Driver $driver,
        protected int $ttl,
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

        $expiresAt = new DateTimeImmutable('+' . $this->ttl .' seconds');

        $this->driver->set(
            key: $this->cacheKey,
            cachedResponse: new CachedResponse(RecordedResponse::fromResponse($response), $expiresAt, $this->ttl)
        );
    }
}
