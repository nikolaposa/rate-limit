<?php

declare(strict_types=1);

namespace RateLimit\Tests;

use RateLimit\RateLimiter;
use RateLimit\ArrayRateLimiter;

class ArrayRateLimiterTest extends RateLimiterTest
{
    protected function getRateLimiter(): RateLimiter
    {
        return new ArrayRateLimiter();
    }
}
