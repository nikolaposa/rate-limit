<?php

declare(strict_types=1);

namespace RateLimit\Exception;

use RateLimit\Rate;
use RuntimeException;

final class LimitExceeded extends RuntimeException implements RateLimitException
{
    /** @var string */
    private $identifier;

    /** @var Rate */
    private $rate;

    public static function for(string $identifier, Rate $rate): self
    {
        $exception = new self(sprintf(
            'Limit has been exceeded for identifier "%s".',
            $identifier
        ));

        $exception->identifier = $identifier;
        $exception->rate = $rate;

        return $exception;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getRate(): Rate
    {
        return $this->rate;
    }
}
