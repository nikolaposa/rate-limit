<?php

declare(strict_types=1);

namespace RateLimit\Tests;

use RateLimit\RateLimiter;
use RateLimit\InMemoryRateLimiter;

class InMemoryRateLimiterTest extends RateLimiterTest
{
    protected function getRateLimiter(): RateLimiter
    {
        return new InMemoryRateLimiter();
    }
}
