<?php

declare(strict_types=1);

namespace RateLimit;

interface SilentRateLimiter
{
    public function limitSilently(string $identifier, Rate $rate): Status;
}
