<?php

declare(strict_types=1);

namespace RateLimit;

use RateLimit\Exception\LimitExceeded;
use function floor;
use function time;

final class InMemoryRateLimiter implements RateLimiter, SilentRateLimiter
{
    /** @var array */
    private $store = [];

    public function limit(string $identifier, Rate $rate): void
    {
        $key = $this->key($identifier, $rate->getInterval());

        $current = $this->hit($key, $rate);

        if ($current > $rate->getOperations()) {
            throw LimitExceeded::for($identifier, $rate);
        }
    }

    public function limitSilently(string $identifier, Rate $rate): Status
    {
        $key = $this->key($identifier, $rate->getInterval());

        $current = $this->hit($key, $rate);

        return Status::from(
            $identifier,
            $current,
            $rate->getOperations(),
            $this->store[$key]['reset_time']
        );
    }

    private function key(string $identifier, int $interval): string
    {
        return "$identifier:$interval:" . floor(time() / $interval);
    }

    private function hit(string $key, Rate $rate): int
    {
        if (!isset($this->store[$key])) {
            $this->store[$key] = [
                'current' => 1,
                'reset_time' => time() + $rate->getInterval(),
            ];
        } elseif ($this->store[$key]['current'] <= $rate->getOperations()) {
            $this->store[$key]['current']++;
        }

        return $this->store[$key]['current'];
    }
}
