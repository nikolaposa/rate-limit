<?php

declare(strict_types=1);

namespace RateLimit\Tests;

use RateLimit\ApcuRateLimiter;
use RateLimit\Exception\CannotUseRateLimiter;
use RateLimit\Rate;
use RateLimit\RateLimiter;
use function apcu_clear_cache;

class ApcuRateLimiterTest extends RateLimiterTest
{
    protected function getRateLimiter(Rate $rate): RateLimiter
    {
        try {
            $rateLimiter = new ApcuRateLimiter($rate);
        } catch (CannotUseRateLimiter $exception) {
            $this->markTestSkipped($exception->getMessage());
        }

        apcu_clear_cache();

        return $rateLimiter;
    }
}
