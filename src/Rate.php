<?php

declare(strict_types=1);

namespace RateLimit;

class Rate
{
    /** @var int */
    protected $operations;

    /** @var int */
    protected $interval;

    final protected function __construct(int $operations, int $interval)
    {
        if ($operations <= 0) {
            throw new \InvalidArgumentException('Quota must be greater than zero');
        }

        if ($interval <= 0) {
            throw new \InvalidArgumentException('Seconds interval must be greater than zero');
        }

        $this->operations = $operations;
        $this->interval = $interval;
    }

    public static function perSecond(int $operations)
    {
        return new static($operations, 1);
    }

    public static function perMinute(int $operations)
    {
        return new static($operations, 60);
    }

    public static function perHour(int $operations)
    {
        return new static($operations, 3600);
    }

    public static function perDay(int $operations)
    {
        return new static($operations, 86400);
    }

    public static function custom(int $operations, int $interval)
    {
        return new static($operations, $interval);
    }

    public function getOperations(): int
    {
        return $this->operations;
    }

    public function getInterval(): int
    {
        return $this->interval;
    }
}
