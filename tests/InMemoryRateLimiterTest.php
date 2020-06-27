<?php

declare(strict_types=1);

namespace RateLimit\Tests;

use RateLimit\InMemoryRateLimiter;
use RateLimit\RateLimiter;

class InMemoryRateLimiterTest extends RateLimiterTest
{
    protected function getRateLimiter(): RateLimiter
    {
        return new InMemoryRateLimiter();
    }
}
