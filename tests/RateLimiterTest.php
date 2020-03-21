<?php

declare(strict_types=1);

namespace RateLimit\Tests;

use PHPUnit\Framework\TestCase;
use RateLimit\Rate;
use RateLimit\RateLimiter;

abstract class RateLimiterTest extends TestCase
{
    abstract protected function getRateLimiter(): RateLimiter;

    /**
     * @test
     */
    public function it_maintains_number_of_remaining_attempts(): void
    {
        $status = $this->getRateLimiter()->handle('test', Rate::perMinute(10));

        $this->assertFalse($status->quotaExceeded());
        $this->assertEquals(9, $status->getRemainingAttempts());
    }

    /**
     * @test
     */
    public function it_returns_correct_status_when_quota_is_exceeded(): void
    {
        $rateLimiter = $this->getRateLimiter();
        $rate = Rate::perHour(1);

        $rateLimiter->handle('test', $rate);
        $rateLimiter->handle('test', $rate);
        $status = $rateLimiter->handle('test', $rate);

        $this->assertTrue($status->quotaExceeded());
        $this->assertSame(2, $status->getCurrent());
        $this->assertSame(0, $status->getRemainingAttempts());
    }

    /**
     * @test
     */
    public function it_resets_quota_after_rate_interval(): void
    {
        $rateLimiter = $this->getRateLimiter();
        $rate = Rate::perSecond(10);

        $rateLimiter->handle('test', $rate);
        $rateLimiter->handle('test', $rate);
        $rateLimiter->handle('test', $rate);
        sleep(2);
        $status = $rateLimiter->handle('test', $rate);

        $this->assertFalse($status->quotaExceeded());
        $this->assertSame(9, $status->getRemainingAttempts());
    }
}
