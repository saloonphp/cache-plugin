<?php

namespace Sammyjo20\SaloonCachePlugin\Data;

use Carbon\CarbonInterface;
use GuzzleHttp\Psr7\PumpStream;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\ResponseInterface;
use Sammyjo20\SaloonCachePlugin\Helpers\BodyStore;

class CachedResponse
{
    /**
     * @param CarbonInterface $expiry
     * @param ResponseInterface $response
     */
    public function __construct(
        protected CarbonInterface $expiry,
        protected ResponseInterface $response,
    ) {
        //
    }

    /**
     * Check if the cached response is valid.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->expiry->isFuture();
    }

    /**
     * Check if the cached response is invalid.
     *
     * @return bool
     */
    public function isInvalid(): bool
    {
        return ! $this->isValid();
    }

    /**
     * Retrieve the response.
     *
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * The expiry datetime of the cache response.
     *
     * @return CarbonInterface
     */
    public function getExpiry(): CarbonInterface
    {
        return $this->expiry;
    }

    /**
     * Handle the falling asleep of the cached response. We'll convert it into a "PumpStream" which
     * can be converted into a stream while waking up.
     *
     * @return array
     */
    public function __sleep(): array
    {
        $body = $this->response->getBody();
        $bodyData = $body->getContents();

        $this->response = $this->response->withBody(
            new PumpStream(
                new BodyStore($bodyData),
                [
                    'size' => mb_strlen($bodyData),
                ]
            )
        );

        // Rewind the stream, so it is reset for the next handler
        // reading the response

        if ($body->isSeekable()) {
            $body->rewind();
        }

        return array_keys(get_object_vars($this));
    }

    /**
     * Handle the waking up of the class (being unserialized)
     *
     * @return void
     */
    public function __wakeup()
    {
        $body = $this->response->getBody();

        $this->response = $this->response->withBody(Utils::streamFor($body));
    }
}
