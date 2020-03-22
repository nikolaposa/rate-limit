<?php

declare(strict_types=1);

namespace RateLimit\Tests;

use PHPUnit\Framework\TestCase;
use RateLimit\Exception\LimitExceeded;
use RateLimit\Rate;
use RateLimit\RateLimiter;
use RateLimit\SilentRateLimiter;

abstract class RateLimiterTest extends TestCase
{
    abstract protected function getRateLimiter(): RateLimiter;

    /**
     * @test
     */
    public function it_raises_exception_when_limit_is_exceeded(): void
    {
        $rateLimiter = $this->getRateLimiter();
        $identifier = 'test';
        $rate = Rate::perHour(1);

        $rateLimiter->limit($identifier, $rate);

        try {
            $rateLimiter->limit($identifier, $rate);

            $this->fail('Limit should have been reached');
        } catch (LimitExceeded $exception) {
            $this->assertSame("Limit of has been exceeded by identifier: $identifier", $exception->getMessage());
            $this->assertSame($identifier, $exception->getIdentifier());
            $this->assertSame($rate, $exception->getRate());
        }
    }

    /**
     * @test
     */
    public function it_resets_limit_after_rate_interval(): void
    {
        $rateLimiter = $this->getRateLimiter();
        $identifier = 'test';
        $rate = Rate::perSecond(1);

        $rateLimiter->limit($identifier, $rate);
        sleep(2);
        $rateLimiter->limit($identifier, $rate);

        try {
            $rateLimiter->limit($identifier, $rate);

            $this->fail('Limit should have been reached');
        } catch (LimitExceeded $exception) {
            $this->assertSame($identifier, $exception->getIdentifier());
        }
    }

    /**
     * @test
     */
    public function it_silently_returns_correct_status_when_limit_is_exceeded(): void
    {
        $rateLimiter = $this->getRateLimiter();

        if (!$rateLimiter instanceof SilentRateLimiter) {
            $this->markTestSkipped('RateLimiter not capable of silent limiting');
        }

        $identifier = 'test';
        $rate = Rate::perHour(1);

        $rateLimiter->limitSilently($identifier, $rate);
        $rateLimiter->limitSilently($identifier, $rate);
        $status = $rateLimiter->limitSilently($identifier, $rate);

        $this->assertTrue($status->limitExceeded());
        $this->assertSame(0, $status->getRemainingAttempts());
    }

    /**
     * @test
     */
    public function it_silently_tracks_rate_limit_status_information(): void
    {
        $rateLimiter = $this->getRateLimiter();

        if (!$rateLimiter instanceof SilentRateLimiter) {
            $this->markTestSkipped('RateLimiter not capable of silent limiting');
        }

        $identifier = 'test';
        $rate = Rate::perMinute(10);
        
        $status = $rateLimiter->limitSilently($identifier, $rate);

        $this->assertSame($identifier, $status->getIdentifier());
        $this->assertFalse($status->limitExceeded());
        $this->assertSame($rate->getOperations(), $status->getLimit());
        $this->assertSame(9, $status->getRemainingAttempts());
    }

    /**
     * @test
     */
    public function it_silently_resets_limit_after_rate_interval(): void
    {
        $rateLimiter = $this->getRateLimiter();

        if (!$rateLimiter instanceof SilentRateLimiter) {
            $this->markTestSkipped('RateLimiter not capable of silent limiting');
        }

        $identifier = 'test';
        $rate = Rate::perSecond(10);

        $rateLimiter->limitSilently($identifier, $rate);
        $rateLimiter->limitSilently($identifier, $rate);
        $rateLimiter->limitSilently($identifier, $rate);
        sleep(2);
        $status = $rateLimiter->limitSilently($identifier, $rate);

        $this->assertFalse($status->limitExceeded());
        $this->assertSame(9, $status->getRemainingAttempts());
    }
}
