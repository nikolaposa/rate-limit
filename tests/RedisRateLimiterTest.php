<?php

declare(strict_types=1);

namespace RateLimit\Tests;

use RateLimit\Rate;
use RateLimit\RateLimiter;
use RateLimit\RedisRateLimiter;
use Redis;
use function extension_loaded;

class RedisRateLimiterTest extends RateLimiterTest
{
    protected function getRateLimiter(Rate $rate): RateLimiter
    {
        if (!extension_loaded('redis')) {
            $this->markTestSkipped('Redis extension not loaded.');
        }

        $redis = new Redis();

        $success = @ $redis->connect('127.0.0.1', 6379);

        if (!$success) {
            $this->markTestSkipped('Cannot connect to Redis.');
        }

        $redis->flushDB();

        return new RedisRateLimiter($rate, $redis);
    }
}
