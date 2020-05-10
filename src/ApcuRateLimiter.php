<?php

declare(strict_types=1);

namespace RateLimit;

use RateLimit\Exception\CannotUseRateLimiter;
use RateLimit\Exception\LimitExceeded;

final class ApcuRateLimiter implements RateLimiter, SilentRateLimiter
{
    /** @var string */
    private $keyPrefix;

    public function __construct(string $keyPrefix = '')
    {
        if (!\extension_loaded('apcu') || \ini_get('apc.enabled') === '0') {
            throw new CannotUseRateLimiter('APCu extension is not loaded or not enabled.');
        }

        if (\ini_get('apc.use_request_time') === '1') {
            throw new CannotUseRateLimiter('APCu ini configuration "apc.use_request_time" should be set to "0".');
        }

        $this->keyPrefix = $keyPrefix;
    }

    public function limit(string $identifier, Rate $rate): void
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

    public function limitSilently(string $identifier, Rate $rate): Status
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
        return "{$this->keyPrefix}{$identifier}:value:$interval";
    }

    private function timeKey(string $identifier, int $interval): string
    {
        return "{$this->keyPrefix}{$identifier}:time:$interval";
    }

    private function getCurrent(string $valueKey): int
    {
        return (int) \apcu_fetch($valueKey);
    }

    private function updateCounter(string $valueKey, string $timeKey, int $interval): int
    {
        $current = \apcu_inc($valueKey, 1, $success, $interval);

        if ($current === 1) {
            \apcu_store($timeKey, \time(), $interval);
        }

        return $current === false ? 1 : $current;
    }

    protected function getElapsedTime(string $timeKey): int
    {
        return \time() - (int) \apcu_fetch($timeKey);
    }
}
