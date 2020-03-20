<?php

declare(strict_types=1);

namespace RateLimit;

use Assert\Assertion;

class QuotaPolicy
{
    /** @var int */
    protected $quota;

    /** @var int */
    protected $interval;

    final protected function __construct(int $quota, int $interval)
    {
        Assertion::greaterThan($quota, 0);
        Assertion::greaterThan($interval, 0);

        $this->quota = $quota;
        $this->interval = $interval;
    }

    public static function perSecond(int $quota)
    {
        return new static($quota, 1);
    }

    public static function perMinute(int $quota)
    {
        return new static($quota, 60);
    }

    public static function perHour(int $quota)
    {
        return new static($quota, 3600);
    }

    public function getQuota(): int
    {
        return $this->quota;
    }

    public function getInterval(): int
    {
        return $this->interval;
    }
}
