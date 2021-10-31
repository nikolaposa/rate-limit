<?php

declare(strict_types=1);

namespace RateLimit;

use DateTimeImmutable;
use function max;

class Status
{
    protected string $identifier;
    protected bool $success;
    protected int $limit;
    protected int $remainingAttempts;
    protected DateTimeImmutable $resetAt;

    final protected function __construct(string $identifier, bool $success, int $limit, int $remainingAttempts, DateTimeImmutable $resetAt)
    {
        $this->identifier = $identifier;
        $this->success = $success;
        $this->limit = $limit;
        $this->remainingAttempts = $remainingAttempts;
        $this->resetAt = $resetAt;
    }

    public static function from(string $identifier, int $current, int $limit, int $resetTime)
    {
        return new static(
            $identifier,
            $current <= $limit,
            $limit,
            max(0, $limit - $current),
            new DateTimeImmutable("@$resetTime")
        );
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function limitExceeded(): bool
    {
        return !$this->success;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getRemainingAttempts(): int
    {
        return $this->remainingAttempts;
    }

    public function getResetAt(): DateTimeImmutable
    {
        return $this->resetAt;
    }
}
