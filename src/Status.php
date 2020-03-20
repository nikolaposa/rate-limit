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

    /** @var QuotaPolicy */
    protected $quotaPolicy;

    /** @var DateTimeImmutable */
    protected $resetAt;

    final protected function __construct(string $identifier, int $current, QuotaPolicy $quotaPolicy, DateTimeImmutable $resetAt)
    {
        $this->identifier = $identifier;
        $this->current = $current;
        $this->quotaPolicy = $quotaPolicy;
        $this->resetAt = $resetAt;
    }

    public static function from(string $identifier, int $current, QuotaPolicy $quotaPolicy, DateTimeImmutable $resetAt)
    {
        return new static($identifier, $current, $quotaPolicy, $resetAt);
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getCurrent(): int
    {
        return $this->current;
    }

    public function getQuota(): int
    {
        return $this->quotaPolicy->getQuota();
    }

    public function getResetAt(): DateTimeImmutable
    {
        return $this->resetAt;
    }

    public function quotaExceeded(): bool
    {
        return $this->current > $this->getQuota();
    }

    public function getRemainingAttempts(): int
    {
        return max(0, $this->getQuota() - $this->current);
    }
}
