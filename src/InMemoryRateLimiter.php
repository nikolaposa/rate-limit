<?php

declare(strict_types=1);

namespace RateLimit;

use RateLimit\Exception\LimitExceeded;
use function floor;
use function time;

final class InMemoryRateLimiter extends ConfigurableRateLimiter implements RateLimiter, SilentRateLimiter
{
    private array $store = [];

    public function limit(string $identifier): void
    {
        $key = $this->key($identifier);

        $current = $this->hit($key);

        if ($current > $this->rate->getOperations()) {
            throw LimitExceeded::for($identifier, $this->rate);
        }
    }

    public function limitSilently(string $identifier): Status
    {
        $key = $this->key($identifier);

        $current = $this->hit($key);

        return Status::from(
            $identifier,
            $current,
            $this->rate->getOperations(),
            $this->store[$key]['reset_time']
        );
    }

    private function key(string $identifier): string
    {
        $interval = $this->rate->getInterval();

        return "$identifier:$interval:" . floor(time() / $interval);
    }

    private function hit(string $key): int
    {
        if (!isset($this->store[$key])) {
            $this->store[$key] = [
                'current' => 1,
                'reset_time' => time() + $this->rate->getInterval(),
            ];
        } elseif ($this->store[$key]['current'] <= $this->rate->getOperations()) {
            $this->store[$key]['current']++;
        }

        return $this->store[$key]['current'];
    }
}
