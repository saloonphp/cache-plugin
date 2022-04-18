<?php

namespace Sammyjo20\SaloonCachePlugin\Http;

use GuzzleHttp\Psr7\Utils;
use Carbon\CarbonInterface;
use GuzzleHttp\Psr7\PumpStream;
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

    public function __sleep(): array
    {
        $responseBody = (string)$this->response->getBody();

        $this->response = $this->response->withBody(
            new PumpStream(
                new BodyStore($responseBody),
                [
                    'size' => mb_strlen($responseBody),
                ]
            )
        );

        return array_keys(get_object_vars($this));
    }

    /**
     * @return void
     */
    public function __wakeup()
    {
        // $this->response = $this->response->withBody(Utils::streamFor((string)$this->response->getBody()));
    }
}
