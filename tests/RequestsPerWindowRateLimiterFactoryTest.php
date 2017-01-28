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
use RateLimit\RequestsPerWindowRateLimiterFactory;
use RateLimit\RequestsPerWindowRateLimiter;
use RateLimit\Options\RequestsPerWindowOptions;

/**
 * @author Nikola Posa <posa.nikola@gmail.com>
 */
class RequestsPerWindowRateLimiterFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_creates_in_memory_rate_limiter()
    {
        $rateLimiter = RequestsPerWindowRateLimiterFactory::createInMemoryRateLimiter();

        $this->assertInstanceOf(RequestsPerWindowRateLimiter::class, $rateLimiter);
    }

    /**
     * @test
     */
    public function it_creates_redis_backed_rate_limiter()
    {
        $rateLimiter = RequestsPerWindowRateLimiterFactory::createRedisBackedRateLimiter();

        $this->assertInstanceOf(RequestsPerWindowRateLimiter::class, $rateLimiter);
    }

    /**
     * @test
     */
    public function it_creates_default_options()
    {
        $options = RequestsPerWindowRateLimiterFactory::createOptions();

        $this->assertInstanceOf(RequestsPerWindowOptions::class, $options);
        $this->assertEquals(RequestsPerWindowRateLimiterFactory::DEFAULT_LIMIT, $options->getLimit());
        $this->assertEquals(RequestsPerWindowRateLimiterFactory::DEFAULT_WINDOW, $options->getWindow());
    }

    /**
     * @test
     */
    public function it_creates_preferred_options()
    {
        $options = RequestsPerWindowRateLimiterFactory::createOptions([
            'limit' => 1000,
            'window' => 120,
        ]);

        $this->assertInstanceOf(RequestsPerWindowOptions::class, $options);
        $this->assertEquals(1000, $options->getLimit());
        $this->assertEquals(120, $options->getWindow());
    }
}
