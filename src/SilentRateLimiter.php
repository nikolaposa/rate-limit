<?php

declare(strict_types=1);

namespace RateLimit;

interface SilentRateLimiter
{
    public function control(string $identifier, Rate $rate): Status;
}
