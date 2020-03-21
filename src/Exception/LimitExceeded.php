<?php

declare(strict_types=1);

namespace RateLimit\Exception;

use RateLimit\Rate;
use RuntimeException;

final class LimitExceeded extends RuntimeException
{
    /** @var string */
    protected $identifier;

    /** @var Rate */
    protected $rate;

    public static function for(string $identifier, Rate $rate): self
    {
        $exception = new self("Limit of has been exceeded by identifier: $identifier");
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
