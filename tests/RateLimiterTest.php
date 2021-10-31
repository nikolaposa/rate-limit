<?php

declare(strict_types=1);

namespace RateLimit\Tests;

use PHPUnit\Framework\TestCase;
use RateLimit\Exception\LimitExceeded;
use RateLimit\Rate;
use RateLimit\RateLimiter;
use RateLimit\SilentRateLimiter;
use function sleep;

abstract class RateLimiterTest extends TestCase
{
    abstract protected function getRateLimiter(Rate $rate): RateLimiter;

    /**
     * @test
     */
    public function it_raises_exception_when_limit_is_exceeded(): void
    {
        $rate = Rate::perHour(1);
        $rateLimiter = $this->getRateLimiter($rate);
        $identifier = 'test';

        $rateLimiter->limit($identifier);

        try {
            $rateLimiter->limit($identifier);

            $this->fail('Limit should have been reached');
        } catch (LimitExceeded $exception) {
            $this->assertSame($identifier, $exception->getIdentifier());
            $this->assertSame($rate, $exception->getRate());
        }
    }

    /**
     * @test
     */
    public function it_resets_limit_after_rate_interval(): void
    {
        $rateLimiter = $this->getRateLimiter(Rate::perSecond(1));
        $identifier = 'test';

        $rateLimiter->limit($identifier);
        sleep(2);
        $rateLimiter->limit($identifier);

        try {
            $rateLimiter->limit($identifier);

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
        $rateLimiter = $this->getRateLimiter(Rate::perHour(1));

        if (!$rateLimiter instanceof SilentRateLimiter) {
            $this->markTestSkipped('RateLimiter not capable of silent limiting');
        }

        $identifier = 'test';

        $rateLimiter->limitSilently($identifier);
        $rateLimiter->limitSilently($identifier);
        $status = $rateLimiter->limitSilently($identifier);

        $this->assertTrue($status->limitExceeded());
        $this->assertSame(0, $status->getRemainingAttempts());
    }

    /**
     * @test
     */
    public function it_silently_tracks_rate_limit_status_information(): void
    {
        $rate = Rate::perMinute(10);
        $rateLimiter = $this->getRateLimiter($rate);

        if (!$rateLimiter instanceof SilentRateLimiter) {
            $this->markTestSkipped('RateLimiter not capable of silent limiting');
        }

        $identifier = 'test';

        $status = $rateLimiter->limitSilently($identifier);

        $this->assertSame($identifier, $status->getIdentifier());
        $this->assertFalse($status->limitExceeded());
        $this->assertSame($rate->getOperations(), $status->getLimit());
        $this->assertSame($rate->getOperations() - 1, $status->getRemainingAttempts());
    }

    /**
     * @test
     */
    public function it_silently_resets_limit_after_rate_interval(): void
    {
        $rate = Rate::perSecond(10);
        $rateLimiter = $this->getRateLimiter($rate);

        if (!$rateLimiter instanceof SilentRateLimiter) {
            $this->markTestSkipped('RateLimiter not capable of silent limiting');
        }

        $identifier = 'test';

        $rateLimiter->limitSilently($identifier);
        $rateLimiter->limitSilently($identifier);
        $rateLimiter->limitSilently($identifier);
        sleep(2);
        $status = $rateLimiter->limitSilently($identifier);

        $this->assertFalse($status->limitExceeded());
        $this->assertSame($rate->getOperations() - 1, $status->getRemainingAttempts());
    }
}
