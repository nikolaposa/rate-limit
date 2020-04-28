<?php

declare(strict_types=1);

namespace RateLimit;

use Redis;

final class RedisRateLimiter extends AbstractTTLRateLimiter
{
    /** @var Redis */
    private $redis;

    public function __construct(Redis $predis, string $keyPrefix = '')
    {
        if (!\extension_loaded('redis')) {
            throw new \RuntimeException('Redis extension is not loaded.');
        }

        parent::__construct($keyPrefix);

        $this->redis = $predis;
    }

    protected function getCurrent(string $key): int
    {
        return (int) $this->redis->get($key);
    }

    protected function updateCounter(string $key, int $interval): int
    {
        $current = $this->redis->incr($key);

        if ($current === 1) {
            $this->redis->expire($key, $interval);
        }

        return $current;
    }

    protected function ttl(string $key): int
    {
        return max((int) ceil($this->redis->pttl($key) / 1000), 0);
    }
}
