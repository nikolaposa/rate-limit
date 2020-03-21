<?php

declare(strict_types=1);

namespace RateLimit\Tests\TestAsset;

use DateTimeImmutable;
use RateLimit\Rate;
use RateLimit\RateLimiter;
use RateLimit\Status;

final class InMemoryRateLimiter implements RateLimiter
{
    /** @var array */
    private $store = [];

    public function handle(string $identifier, Rate $rate): Status
    {
        $key = "$identifier:{$rate->getInterval()}";

        if (!isset($this->store[$key]) || time() > $this->store[$key]['expires']) {
            $this->store[$key] = [
                'current' => 1,
                'expires' => time() + $rate->getInterval(),
            ];
        } elseif ($this->store[$key]['current'] <= $rate->getQuota()) {
            $this->store[$key]['current']++;
        }

        return Status::from(
            $identifier,
            $this->store[$key]['current'],
            $rate,
            new DateTimeImmutable('@' . $this->store[$key]['expires'])
        );
    }
}
