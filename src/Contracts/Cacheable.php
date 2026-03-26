<?php

declare(strict_types=1);

namespace Saloon\CachePlugin\Contracts;

use DateTimeImmutable;
use Saloon\Http\Response;

interface Cacheable
{
    /**
     * Resolve the driver responsible for caching
     */
    public function resolveCacheDriver(): Driver;

    /**
     * Resolve the cache expiry in seconds or as an DateTimeImmutable
     */
    public function resolveCacheExpiry(Response $response): DateTimeImmutable|int;
}
