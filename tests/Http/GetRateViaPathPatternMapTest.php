<?php

declare(strict_types=1);

namespace RateLimit\Tests\Http;

use Laminas\Diactoros\ServerRequestFactory;
use PHPUnit\Framework\TestCase;
use RateLimit\Http\GetRateViaPathPatternMap;
use RateLimit\Rate;

class GetRateViaPathPatternMapTest extends TestCase
{
    /**
     * @test
     */
    public function it_gets_rate_for_first_path_matched(): void
    {
        $getRate = new GetRateViaPathPatternMap([
            '|/api/posts|' => Rate::perSecond(10),
            '|/api/albums/[0-9]+|' => Rate::perMinute(100),
            '|/api/comments|' => Rate::perHour(1000),
        ]);
        $request = (new ServerRequestFactory())->createServerRequest('POST', '/api/albums/123');

        $rate = $getRate->forRequest($request);

        $this->assertSame(100, $rate->getQuota());
        $this->assertSame(60, $rate->getInterval());
    }

    /**
     * @test
     */
    public function it_returns_no_rate_if_path_not_matched(): void
    {
        $getRate = new GetRateViaPathPatternMap([
            '|/api/users|' => Rate::perSecond(10),
        ]);
        $request = (new ServerRequestFactory())->createServerRequest('POST', '/foo/bar');

        $rate = $getRate->forRequest($request);

        $this->assertNull($rate);
    }
}
