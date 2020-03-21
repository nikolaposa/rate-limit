<?php

declare(strict_types=1);

namespace RateLimit;

use DateTimeImmutable;
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
        $key = $this->key($identifier, $rate->getInterval());

        $current = $this->getCurrent($key);

        if ($current > $rate->getOperations()) {
            throw LimitExceeded::for($identifier, $rate);
        }

        $this->updateCounter($key, $rate->getInterval());
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
            $rate,
            (new DateTimeImmutable())->modify('+' . $this->ttl($key) . ' seconds')
        );
    }

    private function key(string $identifier, int $interval): string
    {
        return "{$this->keyPrefix}:{$interval}:$identifier";
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
