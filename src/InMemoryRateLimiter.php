<?php

declare(strict_types=1);

namespace RateLimit;

use DateTimeImmutable;

final class InMemoryRateLimiter implements RateLimiter
{
    /** @var array */
    private $store = [];

    public function handle(string $identifier, QuotaPolicy $quotaPolicy): Status
    {
        $key = "$identifier:{$quotaPolicy->getInterval()}";

        if (!isset($this->store[$key]) || time() > $this->store[$key]['expires']) {
            $this->store[$key] = [
                'current' => 1,
                'expires' => time() + $quotaPolicy->getInterval(),
            ];
        } elseif ($this->store[$key]['current'] <= $quotaPolicy->getQuota()) {
            $this->store[$key]['current']++;
        }

        return Status::from(
            $identifier,
            $this->store[$key]['current'],
            $quotaPolicy,
            new DateTimeImmutable('@' . $this->store[$key]['expires'])
        );
    }
}
