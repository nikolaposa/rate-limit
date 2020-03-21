<?php

declare(strict_types=1);

namespace RateLimit;

use DateTimeImmutable;

class Status
{
    /** @var string */
    protected $identifier;

    /** @var int */
    protected $current;

    /** @var Rate */
    protected $rate;

    /** @var DateTimeImmutable */
    protected $resetAt;

    final protected function __construct(string $identifier, int $current, Rate $rate, DateTimeImmutable $resetAt)
    {
        $this->identifier = $identifier;
        $this->current = $current;
        $this->rate = $rate;
        $this->resetAt = $resetAt;
    }

    public static function from(string $identifier, int $current, Rate $rate, DateTimeImmutable $resetAt)
    {
        return new static($identifier, $current, $rate, $resetAt);
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getCurrent(): int
    {
        return $this->current;
    }

    public function getLimit(): int
    {
        return $this->rate->getOperations();
    }

    public function getResetAt(): DateTimeImmutable
    {
        return $this->resetAt;
    }

    public function limitExceeded(): bool
    {
        return $this->current > $this->getLimit();
    }

    public function getRemainingAttempts(): int
    {
        return max(0, $this->getLimit() - $this->current);
    }
}
