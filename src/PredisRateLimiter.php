<?php

declare(strict_types=1);

namespace RateLimit;

use Predis\ClientInterface;
use RateLimit\Exception\LimitExceeded;
use function ceil;
use function max;
use function time;

final class PredisRateLimiter implements RateLimiter, SilentRateLimiter
{
    /** @var ClientInterface */
    private $predis;

    /** @var string */
    private $keyPrefix;

    public function __construct(ClientInterface $predis, string $keyPrefix = '')
    {
        $this->predis = $predis;
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
        return (int) $this->predis->get($key);
    }

    private function updateCounter(string $key, int $interval): int
    {
        $current = $this->predis->incr($key);

        if ($current === 1) {
            $this->predis->expire($key, $interval);
        }

        return $current;
    }

    private function ttl(string $key): int
    {
        return max((int) ceil($this->predis->pttl($key) / 1000), 0);
    }
}
