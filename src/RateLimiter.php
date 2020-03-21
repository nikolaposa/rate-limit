<?php

declare(strict_types=1);

namespace RateLimit;

use RateLimit\Exception\LimitExceeded;

interface RateLimiter
{
    /**
     * @throws LimitExceeded
     */
    public function limit(string $identifier, Rate $rate): void;
}
