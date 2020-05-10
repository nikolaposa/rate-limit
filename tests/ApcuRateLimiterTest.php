<?php

declare(strict_types=1);

namespace RateLimit\Tests;

use RateLimit\ApcuRateLimiter;
use RateLimit\RateLimiter;

class ApcuRateLimiterTest extends RateLimiterTest
{
    protected function getRateLimiter(): RateLimiter
    {
        if (!\extension_loaded('apcu') || \ini_get('apc.enable_cli') === '0') {
            $this->markTestSkipped('APCu extension not loaded or not enabled.');
        }

        if (\ini_get('apc.use_request_time') === '1') {
            $this->markTestSkipped('APCu ini configuration "apc.use_request_time" is not set to "0".');
        }

        \apcu_clear_cache();

        return new ApcuRateLimiter();
    }
}
