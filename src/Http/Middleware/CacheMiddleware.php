<?php

namespace Saloon\CachePlugin\Http\Middleware;

use Saloon\CachePlugin\Contracts\Driver;
use Saloon\CachePlugin\Helpers\CacheKeyHelper;
use Saloon\Contracts\PendingRequest;
use Saloon\Contracts\RequestMiddleware;
use Saloon\Contracts\Response;

class CacheMiddleware implements RequestMiddleware
{
    /**
     * Constructor
     *
     * @param \Saloon\CachePlugin\Contracts\Driver $driver
     * @param int $ttl
     * @param string|null $cacheKey
     */
    public function __construct(
        protected Driver $driver,
        protected int $ttl,
        protected ?string $cacheKey,
    )
    {
        //
    }

    /**
     * Handle the middleware
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @return void
     * @throws \JsonException
     */
    public function __invoke(PendingRequest $pendingRequest): void
    {
        $driver = $this->driver;
        $cacheKey = hash('sha256', $this->cacheKey ?? CacheKeyHelper::create($pendingRequest));

        $cacheFile = $driver->get($cacheKey);

        if (isset($cacheFile)) {
            //
        }

        $pendingRequest->middleware()->onResponse(function (Response $response) {
            dd('yo', $response);
        });

        dd('not set');

        // Todo: Create a checksum of the current request being used and check if it exists
        // Todo: When it exists, return it
        // Todo: When it has expired, delete it
        // Todo: When it does not exist use the RequestRecorder class to record a request

        // $recordedResponse = ResponseRecorder::record($response)
        // $recordedResponse->toFile();

        // To convert back: $recordedResponse = RecordedResponse::fromFile($contents);
        // new CachedResponse($recordedResponse->data, $recordedResponse->statusCode, $recordedResponse->headers)

        dd('yo!', $this->driver, $this->ttl);
    }
}
