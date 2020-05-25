<?php

declare(strict_types=1);

namespace RateLimit\Tests;

use RateLimit\ApcuRateLimiter;
use RateLimit\Exception\CannotUseRateLimiter;
use RateLimit\RateLimiter;

class ApcuRateLimiterTest extends RateLimiterTest
{
    protected function getRateLimiter(): RateLimiter
    {
        try {
            $rateLimiter = new ApcuRateLimiter();
        } catch (CannotUseRateLimiter $exception) {
            $this->markTestSkipped($exception->getMessage());
        }

        \apcu_clear_cache();

        return $rateLimiter;
    }
}
