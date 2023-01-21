<?php

declare(strict_types=1);

namespace Saloon\CachePlugin\Data;

use DateTimeImmutable;
use Saloon\Data\RecordedResponse;
use Saloon\Http\Faking\SimulatedResponsePayload;
use Saloon\Contracts\SimulatedResponsePayload as SimulatedResponsePayloadContract;

class CachedResponse
{
    /**
     * Constructor
     *
     * @param \Saloon\Data\RecordedResponse $recordedResponse
     * @param \DateTimeImmutable $expiresAt
     * @param int $ttl
     */
    public function __construct(
        readonly public RecordedResponse  $recordedResponse,
        readonly public DateTimeImmutable $expiresAt,
        readonly public int $ttl,
    ) {
        //
    }

    /**
     * Check if the response has expired.
     *
     * @return bool
     */
    public function hasExpired(): bool
    {
        return $this->expiresAt->getTimestamp() <= (new DateTimeImmutable)->getTimestamp();
    }

    /**
     * Check if the response has not expired.
     *
     * @return bool
     */
    public function hasNotExpired(): bool
    {
        return ! $this->hasExpired();
    }

    /**
     * Create a simulated response payload
     *
     * @return \Saloon\Contracts\SimulatedResponsePayload
     */
    public function getSimulatedResponsePayload(): SimulatedResponsePayloadContract
    {
        $response = $this->recordedResponse;

        return new SimulatedResponsePayload(
            body: $response->data,
            status: $response->statusCode,
            headers: $response->headers
        );
    }
}
