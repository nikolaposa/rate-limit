<?php

declare(strict_types=1);

namespace RateLimit\Tests\TestAsset;

use DateTimeImmutable;
use RateLimit\Exception\LimitExceeded;
use RateLimit\Rate;
use RateLimit\RateLimiter;
use RateLimit\SilentRateLimiter;
use RateLimit\Status;

final class InMemoryRateLimiter implements RateLimiter, SilentRateLimiter
{
    /** @var array */
    private $store = [];

    public function limit(string $identifier, Rate $rate): void
    {
        $key = "$identifier:{$rate->getInterval()}:" . floor(time() / $rate->getInterval());

        if (!isset($this->store[$key])) {
            $this->store[$key] = [
                'current' => 1,
                'reset_at' => time() + $rate->getInterval(),
            ];
        } elseif ($this->store[$key]['current'] > $rate->getOperations()) {
            throw LimitExceeded::for($identifier, $rate);
        }

        $this->store[$key]['current']++;
    }

    public function limitSilently(string $identifier, Rate $rate): Status
    {
        $key = "$identifier:{$rate->getInterval()}:" . floor(time() / $rate->getInterval());

        if (!isset($this->store[$key])) {
            $this->store[$key] = [
                'current' => 1,
                'reset_at' => time() + $rate->getInterval(),
            ];
        } elseif ($this->store[$key]['current'] <= $rate->getOperations()) {
            $this->store[$key]['current']++;
        }

        return Status::from(
            $identifier,
            $this->store[$key]['current'],
            $rate,
            new DateTimeImmutable('@' . $this->store[$key]['reset_at'])
        );
    }
}
