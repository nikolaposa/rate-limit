<?php

declare(strict_types=1);

namespace RateLimit\Tests;

use PHPUnit\Framework\TestCase;
use RateLimit\QuotaPolicy;
use RateLimit\RateLimiter;

abstract class RateLimiterTest extends TestCase
{
    abstract protected function getRateLimiter(): RateLimiter;

    /**
     * @test
     */
    public function it_maintains_number_of_remaining_attempts(): void
    {
        $status = $this->getRateLimiter()->handle('test', QuotaPolicy::perMinute(10));

        $this->assertFalse($status->quotaExceeded());
        $this->assertEquals(9, $status->getRemainingAttempts());
    }

    /**
     * @test
     */
    public function it_returns_correct_status_when_quota_is_exceeded(): void
    {
        $rateLimiter = $this->getRateLimiter();
        $quotaPolicy = QuotaPolicy::perHour(1);

        $rateLimiter->handle('test', $quotaPolicy);
        $rateLimiter->handle('test', $quotaPolicy);
        $status = $rateLimiter->handle('test', $quotaPolicy);

        $this->assertTrue($status->quotaExceeded());
        $this->assertSame(2, $status->getCurrent());
        $this->assertSame(0, $status->getRemainingAttempts());
    }

    /**
     * @test
     */
    public function it_resets_quota_after_interval_defined_by_policy(): void
    {
        $rateLimiter = $this->getRateLimiter();
        $quotaPolicy = QuotaPolicy::perSecond(10);

        $rateLimiter->handle('test', $quotaPolicy);
        $rateLimiter->handle('test', $quotaPolicy);
        $rateLimiter->handle('test', $quotaPolicy);
        sleep(2);
        $status = $rateLimiter->handle('test', $quotaPolicy);

        $this->assertFalse($status->quotaExceeded());
        $this->assertSame(9, $status->getRemainingAttempts());
    }
}
