<?php

declare(strict_types=1);

namespace Saloon\CachePlugin\Data;

use DateTimeImmutable;
use Saloon\Data\RecordedResponse;
use Saloon\Http\Faking\FakeResponse;
use Saloon\Contracts\FakeResponse as FakeResponseContract;

class CachedResponse
{
    /**
     * Constructor
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
     */
    public function hasExpired(): bool
    {
        return $this->expiresAt->getTimestamp() <= (new DateTimeImmutable)->getTimestamp();
    }

    /**
     * Check if the response has not expired.
     */
    public function hasNotExpired(): bool
    {
        return ! $this->hasExpired();
    }

    /**
     * Create a fake response
     */
    public function getFakeResponse(): FakeResponseContract
    {
        $response = $this->recordedResponse;

        return new FakeResponse(
            body: $response->data,
            status: $response->statusCode,
            headers: $response->headers
        );
    }
}
