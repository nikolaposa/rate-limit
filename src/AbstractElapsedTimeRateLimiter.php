<?php

declare(strict_types=1);

namespace RateLimit;

use RateLimit\Exception\LimitExceeded;

abstract class AbstractElapsedTimeRateLimiter implements RateLimiter, SilentRateLimiter
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
        $interval = $rate->getInterval();
        $valueKey = $this->valueKey($identifier, $interval);
        $timeKey = $this->timeKey($identifier, $interval);

        $current = $this->getCurrent($valueKey);
        if ($current >= $rate->getOperations()) {
            throw LimitExceeded::for($identifier, $rate);
        }

        $this->updateCounter($valueKey, $timeKey, $rate->getInterval());
    }

    final public function limitSilently(string $identifier, Rate $rate): Status
    {
        $interval = $rate->getInterval();
        $valueKey = $this->valueKey($identifier, $interval);
        $timeKey = $this->timeKey($identifier, $interval);

        $current = $this->getCurrent($valueKey);
        if ($current <= $rate->getOperations()) {
            $current = $this->updateCounter($valueKey, $timeKey, $interval);
        }

        return Status::from(
            \sprintf('%s%s', $this->keyPrefix, $identifier),
            $current,
            $rate->getOperations(),
            \time() + \max(0, $interval - $this->getElapsedTime($timeKey))
        );
    }

    private function valueKey(string $identifier, int $interval): string
    {
        return \sprintf('%s%s:value:%d', $this->keyPrefix, $identifier, $interval);
    }

    private function timeKey(string $identifier, int $interval): string
    {
        return \sprintf('%s%s:time:%d', $this->keyPrefix, $identifier, $interval);
    }

    abstract protected function getCurrent(string $valueKey): int;

    abstract protected function updateCounter(string $valueKey, string $timeKey, int $interval): int;

    abstract protected function getElapsedTime(string $timeKey): int;
}
