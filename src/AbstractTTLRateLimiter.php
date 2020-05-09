<?php

declare(strict_types=1);

namespace RateLimit;

use RateLimit\Exception\LimitExceeded;

abstract class AbstractTTLRateLimiter implements RateLimiter, SilentRateLimiter
{
    /**
     * @var string
     */
    private $keyPrefix;

    public function __construct(string $keyPrefix = '')
    {
        $this->keyPrefix = $keyPrefix;
    }

    final public function limit(string $identifier, Rate $rate): void
    {
        $key = $this->key($identifier, $rate->getInterval());

        $current = $this->getCurrent($key);
        if ($current >= $rate->getOperations()) {
            throw LimitExceeded::for($identifier, $rate);
        }

        $this->updateCounter($key, $rate->getInterval());
    }

    final public function limitSilently(string $identifier, Rate $rate): Status
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
        return \sprintf('%s%s:%d', $this->keyPrefix, $identifier, $interval);
    }

    abstract protected function getCurrent(string $key): int;

    abstract protected function updateCounter(string $key, int $interval): int;

    abstract protected function ttl(string $key): int;
}
