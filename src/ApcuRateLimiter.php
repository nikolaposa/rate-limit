<?php

declare(strict_types=1);

namespace RateLimit;

final class ApcuRateLimiter extends AbstractElapsedTimeRateLimiter
{
    public function __construct(string $keyPrefix = '')
    {
        if (!\extension_loaded('apcu') || \ini_get('apc.enable_cli') === '0') {
            throw new \RuntimeException('APCu extension is not loaded or not enabled.');
        }

        if (\ini_get('apc.use_request_time') === '1') {
            throw new \RuntimeException('APCu ini configuration "apc.use_request_time" should be set to "0".');
        }

        parent::__construct($keyPrefix);
    }

    protected function getCurrent(string $valueKey): int
    {
        return (int) \apcu_fetch($valueKey);
    }

    protected function updateCounter(string $valueKey, string $timeKey, int $interval): int
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
