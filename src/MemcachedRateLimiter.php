<?php

declare(strict_types=1);

namespace RateLimit;

use RateLimit\Exception\LimitExceeded;
use Memcached;

final class MemcachedRateLimiter implements RateLimiter, SilentRateLimiter
{
    /** @var Memcached */
    private $memcached;

    /** @var string */
    private $keyPrefix;

    public function __construct(Memcached $memcached, string $keyPrefix = '')
    {
        $this->memcached = $memcached;
        $this->keyPrefix = $keyPrefix;
    }

    public function limit(string $identifier, Rate $rate): void
    {
        $key = $this->key($identifier, $rate->getInterval());

        $current = $this->getCurrent($key);

        if ($current >= $rate->getOperations()) {
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
        return (int) $this->memcached->get($key);
    }

    private function updateCounter(string $key, int $interval): int
    {
        $current = $this->memcached->increment($key, 1, 1);

        if ($current === 1) {
            $this->memcached->touch($key, $interval);
        }

        return $current;
    }

    private function ttl(string $key): int
    {
        // memcached doesn't support retrieving TTL by key
        // of course there are crutches, but we are normal developers
        return 0;
    }
}
