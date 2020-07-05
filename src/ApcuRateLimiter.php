<?php

declare(strict_types=1);

namespace RateLimit;

use RateLimit\Exception\CannotUseRateLimiter;
use RateLimit\Exception\LimitExceeded;
use function apcu_fetch;
use function apcu_inc;
use function apcu_store;
use function extension_loaded;
use function ini_get;
use function max;
use function sprintf;
use function time;

final class ApcuRateLimiter implements RateLimiter, SilentRateLimiter
{
    /** @var string */
    private $keyPrefix;

    public function __construct(string $keyPrefix = '')
    {
        if (!extension_loaded('apcu') || ini_get('apc.enabled') === '0') {
            throw new CannotUseRateLimiter('APCu extension is not loaded or not enabled.');
        }

        if (ini_get('apc.use_request_time') === '1') {
            throw new CannotUseRateLimiter('APCu ini configuration "apc.use_request_time" should be set to "0".');
        }

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
        return (int) apcu_fetch($limitKey);
    }

    private function updateCounterAndTime(string $limitKey, string $timeKey, int $interval): int
    {
        $current = $this->updateCounter($limitKey, $interval);

        if ($current === 1) {
            apcu_store($timeKey, time(), $interval);
        }

        return $current;
    }

    private function updateCounter(string $limitKey, int $interval): int
    {
        $current = apcu_inc($limitKey, 1, $success, $interval);

        return $current === false ? 1 : $current;
    }

    private function getElapsedTime(string $timeKey): int
    {
        return time() - (int) apcu_fetch($timeKey);
    }
}
