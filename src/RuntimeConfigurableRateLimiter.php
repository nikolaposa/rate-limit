<?php

declare(strict_types=1);

namespace RateLimit;

use LogicException;

final class RuntimeConfigurableRateLimiter extends ConfigurableRateLimiter implements RateLimiter, SilentRateLimiter
{
    public function __construct(private RateLimiter|SilentRateLimiter $rateLimiter)
    {
    }

    public function limit(string $identifier, Rate $rate = null): void
    {
        if (!$this->rateLimiter instanceof RateLimiter) {
            throw new LogicException('Decorated Rate Limiter must implement RateLimiter interface');
        }

        if ($this->rateLimiter instanceof ConfigurableRateLimiter) {
            $this->rateLimiter->rate = $rate;
        }

        $this->rateLimiter->limit($identifier);
    }

    public function limitSilently(string $identifier, Rate $rate = null): Status
    {
        if (!$this->rateLimiter instanceof SilentRateLimiter) {
            throw new LogicException('Decorated Rate Limiter must implement SilentRateLimiter interface');
        }

        if ($this->rateLimiter instanceof ConfigurableRateLimiter) {
            $this->rateLimiter->rate = $rate;
        }

        return $this->rateLimiter->limitSilently($identifier);
    }
}
