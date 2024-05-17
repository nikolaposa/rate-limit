<?php

declare(strict_types=1);

namespace RateLimit\Tests;

use RateLimit\Exception\LimitExceeded;
use RateLimit\InMemoryRateLimiter;
use RateLimit\Rate;
use RateLimit\RateLimiter;
use RateLimit\RuntimeConfigurableRateLimiter;

class RuntimeConfigurableRateLimiterTest extends RateLimiterTest
{
    /**
     * @test
     */
    public function it_allows_rate_to_be_configured_at_runtime(): void
    {
        $rate = Rate::perHour(1);
        $rateLimiter = $this->getRateLimiter(Rate::perMinute(100));
        $identifier = 'test';

        $rateLimiter->limit($identifier, $rate);

        try {
            $rateLimiter->limit($identifier, $rate);

            $this->fail('Limit should have been reached');
        } catch (LimitExceeded $exception) {
            $this->assertSame($identifier, $exception->getIdentifier());
            $this->assertSame($rate, $exception->getRate());
        }
    }

    protected function getRateLimiter(Rate $rate): RateLimiter
    {
        return new RuntimeConfigurableRateLimiter(new InMemoryRateLimiter($rate));
    }
}
