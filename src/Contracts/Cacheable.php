<?php

declare(strict_types=1);

namespace Saloon\CachePlugin\Contracts;

use Carbon\CarbonInterface;

/**
 * @method int|CarbonInterface cacheExpiry
 */
interface Cacheable
{
    /**
     * Resolve the driver responsible for caching
     *
     * @return \Saloon\CachePlugin\Contracts\Driver
     */
    public function resolveCacheDriver(): Driver;
}
