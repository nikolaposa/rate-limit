<?php

declare(strict_types=1);

namespace RateLimit\Tests;

use Memcached;
use RateLimit\MemcachedRateLimiter;
use RateLimit\RateLimiter;
use function extension_loaded;

class MemcachedRateLimiterTest extends RateLimiterTest
{
    protected function getRateLimiter(): RateLimiter
    {
        if (!extension_loaded('memcached')) {
            $this->markTestSkipped('Memcached extension not loaded.');
        }

        $memcached = new Memcached('test');
        $memcached->addServer('127.0.0.1', 11211);
        $memcached->setOption(Memcached::OPT_BINARY_PROTOCOL, true);

        $memcached->flush();
        if ($memcached->getResultCode() !== Memcached::RES_SUCCESS) {
            $this->markTestSkipped('Cannot connect to Memcached.');
        }

        return new MemcachedRateLimiter($memcached);
    }
}
