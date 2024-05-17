<?php

declare(strict_types=1);

namespace RateLimit\Tests;

use PHPUnit\Framework\TestCase;
use RateLimit\Exception\LimitExceeded;
use RateLimit\InMemoryRateLimiter;
use RateLimit\Rate;
use RateLimit\RuntimeConfigurableRateLimiter;

class RuntimeConfigurableRateLimiterTest extends TestCase
{
    /**
     * @test
     */
    public function it_allows_rate_to_be_configured_at_runtime(): void
    {
        $rate = Rate::perHour(1);
        $rateLimiter = new RuntimeConfigurableRateLimiter(new InMemoryRateLimiter(Rate::perMinute(100)));
        $identifier = 'test';

        $rateLimiter->limitSilently($identifier, $rate);

        try {
            $rateLimiter->limit($identifier, $rate);

            $this->fail('Limit should have been reached');
        } catch (LimitExceeded $exception) {
            $this->assertSame($identifier, $exception->getIdentifier());
            $this->assertSame($rate, $exception->getRate());
        }
    }
}
