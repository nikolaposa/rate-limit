<?php

declare(strict_types=1);

namespace RateLimit\Tests;

use Predis\Client;
use RateLimit\PredisRateLimiter;
use RateLimit\RateLimiter;

class PredisRateLimiterTest extends RateLimiterTest
{
    protected function getRateLimiter(): RateLimiter
    {
        $predis = new Client();

        $predis->connect();
        if (!$predis->isConnected()) {
            $this->markTestSkipped('Cannot connect with Predis.');
        }

        $predis->flushdb();

        return new PredisRateLimiter($predis);
    }
}
