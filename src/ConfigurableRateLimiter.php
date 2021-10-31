<?php

declare(strict_types=1);

namespace RateLimit;

abstract class ConfigurableRateLimiter
{
    protected Rate $rate;

    public function __construct(Rate $rate)
    {
        $this->rate = $rate;
    }
}
