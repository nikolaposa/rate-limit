<?php

declare(strict_types=1);

namespace RateLimit\Tests;

use Memcached;
use RateLimit\MemcachedRateLimiter;
use RateLimit\RateLimiter;

class MemcachedRateLimiterTest extends RateLimiterTest
{
    protected function getRateLimiter(): RateLimiter
    {
        $memcached = new Memcached();
        $memcached->setOption(Memcached::OPT_BINARY_PROTOCOL, true);

        $success = @ $memcached->addServer('127.0.0.1', 11211);

        if (!$success) {
            $this->markTestSkipped('Cannot connect to Memcached.');
        }

        $memcached->flush();

        return new MemcachedRateLimiter($memcached);
    }
}
