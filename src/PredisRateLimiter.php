<?php

declare(strict_types=1);

namespace RateLimit;

use Predis\ClientInterface;
use RateLimit\Exception\LimitExceeded;
use function ceil;
use function max;
use function time;

final class PredisRateLimiter extends ConfigurableRateLimiter implements RateLimiter, SilentRateLimiter
{
    private ClientInterface $predis;
    private string $keyPrefix;

    public function __construct(Rate $rate, ClientInterface $predis, string $keyPrefix = '')
    {
        parent::__construct($rate);
        $this->predis = $predis;
        $this->keyPrefix = $keyPrefix;
    }

    public function limit(string $identifier): void
    {
        $key = $this->key($identifier);

        $current = $this->getCurrent($key);

        if ($current >= $this->rate->getOperations()) {
            throw LimitExceeded::for($identifier, $this->rate);
        }

        $this->updateCounter($key);
    }

    public function limitSilently(string $identifier): Status
    {
        $key = $this->key($identifier);

        $current = $this->getCurrent($key);

        if ($current <= $this->rate->getOperations()) {
            $current = $this->updateCounter($key);
        }

        return Status::from(
            $identifier,
            $current,
            $this->rate->getOperations(),
            time() + $this->ttl($key)
        );
    }

    private function key(string $identifier): string
    {
        return "{$this->keyPrefix}{$identifier}:{$this->rate->getInterval()}";
    }

    private function getCurrent(string $key): int
    {
        return (int) $this->predis->get($key);
    }

    private function updateCounter(string $key): int
    {
        $current = $this->predis->incr($key);

        if ($current === 1) {
            $this->predis->expire($key, $this->rate->getInterval());
        }

        return $current;
    }

    private function ttl(string $key): int
    {
        return max((int) ceil($this->predis->pttl($key) / 1000), 0);
    }
}
