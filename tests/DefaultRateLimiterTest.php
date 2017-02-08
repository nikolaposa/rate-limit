<?php
/**
 * This file is part of the Rate Limit package.
 *
 * Copyright (c) Nikola Posa
 *
 * For full copyright and license information, please refer to the LICENSE file,
 * located at the package root folder.
 */

declare(strict_types=1);

namespace RateLimit\Tests;

use PHPUnit_Framework_TestCase;
use RateLimit\RateLimiterFactory;

/**
 * @author Nikola Posa <posa.nikola@gmail.com>
 */
class DefaultRateLimiterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_creates_rate_limit_on_initial_hit()
    {
        $rateLimiter = RateLimiterFactory::createInMemoryRateLimiter(5, 3600);

        $rateLimit = $rateLimiter->hit('test');

        $this->assertEquals(5, $rateLimit->getLimit());
        $this->assertEquals(4, $rateLimit->getRemainingAttempts());
        $this->assertGreaterThan(0, $rateLimit->getResetAt());
        $this->assertFalse($rateLimit->isExceeded());
    }

    /**
     * @test
     */
    public function it_creates_rate_limit_with_decreased_remaining_attempts_on_subsequent_hit()
    {
        $rateLimiter = RateLimiterFactory::createInMemoryRateLimiter(5, 3600);

        $rateLimiter->hit('test');

        $rateLimit = $rateLimiter->hit('test');

        $this->assertEquals(5, $rateLimit->getLimit());
        $this->assertEquals(3, $rateLimit->getRemainingAttempts());
        $this->assertGreaterThan(0, $rateLimit->getResetAt());
        $this->assertFalse($rateLimit->isExceeded());
    }

    /**
     * @test
     */
    public function it_creates_exceeded_rate_limit_when_limit_is_reached()
    {
        $rateLimiter = RateLimiterFactory::createInMemoryRateLimiter(1, 3600);

        $rateLimiter->hit('test');

        $rateLimit = $rateLimiter->hit('test');

        $this->assertEquals(5, $rateLimit->getLimit());
        $this->assertEquals(0, $rateLimit->getRemainingAttempts());
        $this->assertGreaterThan(0, $rateLimit->getResetAt());
        $this->assertTrue($rateLimit->isExceeded());
    }

    /**
     * @test
     */
    public function it_resets_rate_limit_after_time_window_passes()
    {
        $rateLimiter = RateLimiterFactory::createInMemoryRateLimiter(1, 1);

        $rateLimiter->hit('test');

        $rateLimit = $rateLimiter->hit('test');

        $this->assertTrue($rateLimit->isExceeded());

        sleep(2);

        $rateLimit = $rateLimiter->hit('test');

        $this->assertFalse($rateLimit->isExceeded());
    }
}
