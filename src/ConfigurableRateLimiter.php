<?php

declare(strict_types=1);

namespace RateLimit;

abstract class ConfigurableRateLimiter
{
    /** @var Rate */
    protected $rate;

    public function __construct(Rate $rate)
    {
        $this->rate = $rate;
    }
}
