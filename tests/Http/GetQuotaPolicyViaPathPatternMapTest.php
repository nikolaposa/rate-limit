<?php

declare(strict_types=1);

namespace RateLimit\Tests\Http;

use Laminas\Diactoros\ServerRequestFactory;
use PHPUnit\Framework\TestCase;
use RateLimit\Http\GetQuotaPolicyViaPathPatternMap;
use RateLimit\QuotaPolicy;

class GetQuotaPolicyViaPathPatternMapTest extends TestCase
{
    /**
     * @test
     */
    public function it_gets_quota_policy_for_first_path_matched(): void
    {
        $getQuotaPolicy = new GetQuotaPolicyViaPathPatternMap([
            '|/api/posts|' => QuotaPolicy::perSecond(10),
            '|/api/albums/[0-9]+|' => QuotaPolicy::perMinute(100),
            '|/api/comments|' => QuotaPolicy::perHour(1000),
        ]);
        $request = (new ServerRequestFactory())->createServerRequest('POST', '/api/albums/123');

        $quotaPolicy = $getQuotaPolicy->forRequest($request);

        $this->assertSame(100, $quotaPolicy->getQuota());
        $this->assertSame(60, $quotaPolicy->getInterval());
    }

    /**
     * @test
     */
    public function it_returns_no_quota_policy_if_path_not_matched(): void
    {
        $getQuotaPolicy = new GetQuotaPolicyViaPathPatternMap([
            '|/api/users|' => QuotaPolicy::perSecond(10),
        ]);
        $request = (new ServerRequestFactory())->createServerRequest('POST', '/foo/bar');

        $quotaPolicy = $getQuotaPolicy->forRequest($request);

        $this->assertNull($quotaPolicy);
    }
}
