<?php

declare(strict_types=1);

namespace RateLimit;

use Memcached;
use RateLimit\Exception\CannotUseRateLimiter;
use RateLimit\Exception\LimitExceeded;
use function max;
use function sprintf;
use function time;

final class MemcachedRateLimiter implements RateLimiter, SilentRateLimiter
{
    private const MEMCACHED_SECONDS_LIMIT = 2592000; // 30 days in seconds

    /** @var Memcached */
    private $memcached;

    /** @var string */
    private $keyPrefix;

    public function __construct(Memcached $memcached, string $keyPrefix = '')
    {
        // @see https://www.php.net/manual/en/memcached.increment.php#111187
        if ($memcached->getOption(Memcached::OPT_BINARY_PROTOCOL) !== 1) {
            throw new CannotUseRateLimiter('Memcached "OPT_BINARY_PROTOCOL" option should be set to "true".');
        }

        $this->memcached = $memcached;
        $this->keyPrefix = $keyPrefix;
    }

    public function limit(string $identifier, Rate $rate): void
    {
        $limitKey = $this->limitKey($identifier, $rate->getInterval());

        $current = $this->getCurrent($limitKey);
        if ($current >= $rate->getOperations()) {
            throw LimitExceeded::for($identifier, $rate);
        }

        $this->updateCounter($limitKey, $rate->getInterval());
    }

    public function limitSilently(string $identifier, Rate $rate): Status
    {
        $interval = $rate->getInterval();
        $limitKey = $this->limitKey($identifier, $interval);
        $timeKey = $this->timeKey($identifier, $interval);

        $current = $this->getCurrent($limitKey);
        if ($current <= $rate->getOperations()) {
            $current = $this->updateCounterAndTime($limitKey, $timeKey, $interval);
        }

        return Status::from(
            $identifier,
            $current,
            $rate->getOperations(),
            time() + max(0, $interval - $this->getElapsedTime($timeKey))
        );
    }

    private function limitKey(string $identifier, int $interval): string
    {
        return sprintf('%s%s:%d', $this->keyPrefix, $identifier, $interval);
    }

    private function timeKey(string $identifier, int $interval): string
    {
        return sprintf('%s%s:%d:time', $this->keyPrefix, $identifier, $interval);
    }

    private function getCurrent(string $limitKey): int
    {
        return (int) $this->memcached->get($limitKey);
    }

    private function updateCounterAndTime(string $limitKey, string $timeKey, int $interval): int
    {
        $current = $this->updateCounter($limitKey, $interval);

        if ($current === 1) {
            $this->memcached->add($timeKey, time(), $this->intervalToMemcachedTime($interval));
        }

        return $current;
    }

    private function updateCounter(string $limitKey, int $interval): int
    {
        $current = $this->memcached->increment($limitKey, 1, 1, $this->intervalToMemcachedTime($interval));

        return $current === false ? 1 : $current;
    }

    private function getElapsedTime(string $timeKey): int
    {
        return time() - (int) $this->memcached->get($timeKey);
    }

    /**
     * Interval to Memcached expiration time.
     *
     * @see https://www.php.net/manual/en/memcached.expiration.php
     *
     * @param int $interval
     * @return int
     */
    private function intervalToMemcachedTime(int $interval): int
    {
        return $interval <= self::MEMCACHED_SECONDS_LIMIT ? $interval : time() + $interval;
    }
}
