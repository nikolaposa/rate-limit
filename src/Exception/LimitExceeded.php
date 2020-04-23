<?php

declare(strict_types=1);

namespace RateLimit\Exception;

use RateLimit\Rate;
use RateLimit\Status;
use RuntimeException;

final class LimitExceeded extends RuntimeException
{
    /** @var Rate */
    protected $rate;

    /** @var Status */
    protected $status;

    public static function for(Status $status, Rate $rate): self
    {
        $exception = new self("Limit of has been exceeded by identifier: {$status->getIdentifier()}");
        $exception->rate = $rate;
        $exception->status = $status;

        return $exception;
    }

    public function getIdentifier(): string
    {
        return $this->status->getIdentifier();
    }

    public function getRate(): Rate
    {
        return $this->rate;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }
}
