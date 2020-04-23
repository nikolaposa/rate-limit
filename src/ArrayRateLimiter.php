<?php

declare(strict_types=1);

namespace RateLimit;

use RateLimit\Exception\LimitExceeded;

final class ArrayRateLimiter implements RateLimiter, SilentRateLimiter
{
    /** @var array */
    private $cache = [];

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
        return "{$identifier}:$interval";
    }

    private function getCurrent(string $key): int
    {
        if (isset($this->cache[$key])) {
            $this->cache[$key] = array_filter($this->cache[$key], function (int $ttl) {
                return $ttl > time();
            });
        }

        return isset($this->cache[$key]) ? count($this->cache[$key]) : 0;
    }

    private function updateCounter(string $key, int $interval): int
    {
        if (!isset($this->cache[$key])) {
            $this->cache[$key] = [];
        }

        array_push($this->cache[$key], time() + $interval);
        $current = count((array) $this->cache[$key]);

        return $current;
    }

    private function ttl(string $key): int
    {
        return max($this->cache[$key] ?? 0);
    }
}
