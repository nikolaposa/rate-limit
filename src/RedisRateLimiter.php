<?php

declare(strict_types=1);

namespace RateLimit;

use DateTimeImmutable;
use Redis;

final class RedisRateLimiter implements RateLimiter
{
    /** @var Redis */
    private $redis;

    /** @var string */
    private $keyPrefix;

    public function __construct(Redis $redis, string $keyPrefix = '')
    {
        $this->redis = $redis;
        $this->keyPrefix = $keyPrefix;
    }

    public function handle(string $identifier, QuotaPolicy $quotaPolicy): Status
    {
        $key = $this->key($identifier, $quotaPolicy->getInterval());

        $current = (int) $this->redis->get($key);

        if ($current <= $quotaPolicy->getQuota()) {
            $current = $this->redis->incr($key);

            if ($current === 1) {
                $this->redis->expire($key, $quotaPolicy->getInterval());
            }
        }

        return Status::from(
            $identifier,
            $current,
            $quotaPolicy,
            (new DateTimeImmutable())->modify('+' . $this->ttl($key) . ' seconds')
        );
    }

    private function key(string $identifier, int $interval): string
    {
        return "{$this->keyPrefix}:{$interval}:$identifier";
    }

    private function ttl(string $key) : int
    {
        return max((int) ceil($this->redis->pttl($key) / 1000), 0);
    }
}
