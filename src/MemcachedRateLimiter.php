<?php

declare(strict_types=1);

namespace RateLimit;

use Memcached;

final class MemcachedRateLimiter extends AbstractElapsedTimeRateLimiter
{
    /** @var Memcached */
    private $memcached;

    public function __construct(Memcached $memcached, string $keyPrefix = '')
    {
        if ($memcached->getOption(Memcached::OPT_BINARY_PROTOCOL) !== 1) {
            throw new \RuntimeException('Memcached "OPT_BINARY_PROTOCOL" option should be set to "true".');
        }

        parent::__construct($keyPrefix);

        $this->memcached = $memcached;
    }

    protected function getCurrent(string $valueKey): int
    {
        return (int) $this->memcached->get($valueKey);
    }

    protected function updateCounter(string $valueKey, string $timeKey, int $interval): int
    {
        $current = $this->memcached->increment($valueKey, 1, 1, $interval);

        if ($current === 1) {
            $this->memcached->add($timeKey, \time(), $interval);
        }

        return $current === false ? 1 : $current;
    }

    protected function getElapsedTime(string $timeKey): int
    {
        return \time() - (int) $this->memcached->get($timeKey);
    }
}
