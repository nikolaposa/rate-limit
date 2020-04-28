<?php

declare(strict_types=1);

namespace RateLimit;

use Predis\ClientInterface;

final class PredisRateLimiter extends AbstractTTLRateLimiter
{
    /** @var ClientInterface */
    private $predis;

    public function __construct(ClientInterface $predis, string $keyPrefix = '')
    {
        parent::__construct($keyPrefix);

        $this->predis = $predis;
    }

    protected function getCurrent(string $key): int
    {
        return (int) $this->predis->get($key);
    }

    protected function updateCounter(string $key, int $interval): int
    {
        $current = $this->predis->incr($key);

        if ($current === 1) {
            $this->predis->expire($key, $interval);
        }

        return $current;
    }

    protected function ttl(string $key): int
    {
        return max((int) ceil($this->predis->pttl($key) / 1000), 0);
    }
}
