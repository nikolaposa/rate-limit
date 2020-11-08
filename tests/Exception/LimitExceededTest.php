<?php

declare(strict_types=1);

namespace RateLimit\Tests\Exception;

use PHPUnit\Framework\TestCase;
use RateLimit\Exception\LimitExceeded;
use RateLimit\Rate;

/**
 * @covers \RateLimit\Exception\LimitExceeded
 */
final class LimitExceededTest extends TestCase
{
    public function testForReturnsException(): void
    {
        $identifier = 'foo';
        $rate = Rate::custom(
            100,
            3600
        );

        $exception = LimitExceeded::for(
            $identifier,
            $rate
        );

        $expected = sprintf(
            'Limit has been exceeded for identifier "%s".',
            $identifier
        );

        self::assertSame($expected, $exception->getMessage());
        self::assertSame($identifier, $exception->getIdentifier());
        self::assertSame($rate, $exception->getRate());
    }
}
