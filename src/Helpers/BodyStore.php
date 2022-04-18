<?php

namespace Sammyjo20\SaloonCachePlugin\Helpers;

/**
 * This object is only meant to provide a callable to `GuzzleHttp\Psr7\PumpStream`.
 *
 * @internal Don't use in your project.
 * @see Credit https://raw.githubusercontent.com/Kevinrob/guzzle-cache-middleware/0a61532ee8bf278a0d875a86a536aeeab592da5a/src/BodyStore.php
 */
class BodyStore
{
    /**
     * @var string
     */
    private string $body;

    /**
     * @var int
     */
    private int $read = 0;

    /**
     * @var int
     */
    private int $toRead;

    /**
     * @param string $body
     */
    public function __construct(string $body)
    {
        $this->body = $body;
        $length = mb_strlen($body);

        $this->toRead = is_int($length) ? $length : 0;
    }

    /**
     * @param int $length
     * @return string|bool
     */
    public function __invoke(int $length): string|bool
    {
        if ($this->toRead <= 0) {
            return false;
        }

        $length = min($length, $this->toRead);

        $body = mb_substr($this->body, $this->read, $length);

        $this->toRead -= $length;
        $this->read += $length;

        return $body;
    }
}
