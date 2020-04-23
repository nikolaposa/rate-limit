<?php

declare(strict_types=1);

namespace RateLimit;

use RateLimit\Exception\LimitExceeded;
use Redis;

final class RedisRateLimiter implements RateLimiter, SilentRateLimiter
{
    /** @var Redis */
    private $redis;

    /** @var string */
    private $keyPrefix;

    public function __construct(Redis $redis, string $keyPrefix = '')
    {
        $this->redis = $redis;
        $this->keyPrefix = $keyPrefix;
    }

    public function limit(string $identifier, Rate $rate): void
    {
        $status = $this->limitSilently($identifier, $rate);

        if ($status->limitExceeded()) {
            throw LimitExceeded::for($status, $rate);
        }
    }

    public function limitSilently(string $identifier, Rate $rate): Status
    {
        $key = $this->key($identifier, $rate->getInterval());

        $current = $this->getCurrent($key);

        if ($current <= $rate->getOperations()) {
            $current = $this->updateCounter($key, $rate->getInterval());
        }

        return Status::from(
            $identifier,
            $current,
            $rate->getOperations(),
            time() + $this->ttl($key)
        );
    }

    private function key(string $identifier, int $interval): string
    {
        return "{$this->keyPrefix}{$identifier}:$interval";
    }

    private function getCurrent(string $key): int
    {
        return (int) $this->redis->get($key);
    }

    private function updateCounter(string $key, int $interval): int
    {
        $current = $this->redis->incr($key);

        if ($current === 1) {
            $this->redis->expire($key, $interval);
        }

        return $current;
    }

    private function ttl(string $key): int
    {
        return max((int) ceil($this->redis->pttl($key) / 1000), 0);
    }
}
