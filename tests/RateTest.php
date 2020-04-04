<?php

declare(strict_types=1);

namespace RateLimit\Tests;

use PHPUnit\Framework\TestCase;
use RateLimit\Rate;

class RateTest extends TestCase
{
    /**
     * @test
     */
    public function it_supports_per_second_ratio(): void
    {
        $rate = Rate::perSecond(10);

        $this->assertSame(10, $rate->getOperations());
        $this->assertSame(1, $rate->getInterval());
    }

    /**
     * @test
     */
    public function it_supports_per_minute_ratio(): void
    {
        $rate = Rate::perMinute(20);

        $this->assertSame(20, $rate->getOperations());
        $this->assertSame(60, $rate->getInterval());
    }

    /**
     * @test
     */
    public function it_supports_per_hour_ratio(): void
    {
        $rate = Rate::perHour(100);

        $this->assertSame(100, $rate->getOperations());
        $this->assertSame(3600, $rate->getInterval());
    }

    /**
     * @test
     */
    public function it_supports_per_day_ratio(): void
    {
        $rate = Rate::perDay(1000);

        $this->assertSame(1000, $rate->getOperations());
        $this->assertSame(86400, $rate->getInterval());
    }

    /**
     * @test
     */
    public function it_supports_custom_ratio(): void
    {
        $rate = Rate::custom(50, 180);

        $this->assertSame(50, $rate->getOperations());
        $this->assertSame(180, $rate->getInterval());
    }
}
