<?php
/**
 * This file is part of the Rate Limit package.
 *
 * Copyright (c) Nikola Posa
 *
 * For full copyright and license information, please refer to the LICENSE file,
 * located at the package root folder.
 */

declare(strict_types=1);

namespace RateLimit\Tests;

use PHPUnit\Framework\TestCase;
use RateLimit\RequestsPerWindowRateLimiter;
use RateLimit\Storage\InMemoryStorage;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;

/**
 * @author Nikola Posa <posa.nikola@gmail.com>
 */
class RequestsPerWindowRateLimiterTest extends TestCase
{
    /**
     * @test
     */
    public function it_sets_rate_limit_headers()
    {
        $rateLimiter = RequestsPerWindowRateLimiter::create(new InMemoryStorage(), [
            'limit' => 5,
            'window' => 3600,
        ]);

        /* @var $response ResponseInterface */
        $response = $rateLimiter(new Request(), new Response());

        $this->assertEquals('5', $response->getHeaderLine(RequestsPerWindowRateLimiter::HEADER_LIMIT));
        $this->assertEquals('4', $response->getHeaderLine(RequestsPerWindowRateLimiter::HEADER_REMAINING));
        $this->assertTrue($response->hasHeader(RequestsPerWindowRateLimiter::HEADER_RESET));
    }

    /**
     * @test
     */
    public function it_sets_appropriate_response_status_when_limit_is_reached()
    {
        $rateLimiter = RequestsPerWindowRateLimiter::create(new InMemoryStorage(), [
            'limit' => 1,
            'window' => 3600,
        ]);

        $rateLimiter(new Request(), new Response());

        /* @var $response ResponseInterface */
        $response = $rateLimiter(new Request(), new Response());

        $this->assertEquals(RequestsPerWindowRateLimiter::LIMIT_EXCEEDED_HTTP_STATUS_CODE, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_does_not_alter_status_code_when_below_the_limit()
    {
        $rateLimiter = RequestsPerWindowRateLimiter::create(new InMemoryStorage(), [
            'limit' => 5,
            'window' => 3600,
        ]);

        /* @var $response ResponseInterface */
        $response = $rateLimiter(new Request(), new Response('php://memory', 200));

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_decrements_remaining_header()
    {
        $rateLimiter = RequestsPerWindowRateLimiter::create(new InMemoryStorage(), [
            'limit' => 5,
            'window' => 3600,
        ]);

        /* @var $response ResponseInterface */
        $response = $rateLimiter(new Request(), new Response());

        $this->assertEquals('4', $response->getHeaderLine(RequestsPerWindowRateLimiter::HEADER_REMAINING));

        /* @var $response ResponseInterface */
        $response = $rateLimiter(new Request(), new Response());

        $this->assertEquals('3', $response->getHeaderLine(RequestsPerWindowRateLimiter::HEADER_REMAINING));
    }

    /**
     * @test
     */
    public function it_resets_rate_limit_after_time_window_passes()
    {
        $rateLimiter = RequestsPerWindowRateLimiter::create(new InMemoryStorage(), [
            'limit' => 1,
            'window' => 2,
        ]);

        /* @var $response ResponseInterface */
        $response = $rateLimiter(new Request(), new Response());

        $this->assertEquals('0', $response->getHeaderLine(RequestsPerWindowRateLimiter::HEADER_REMAINING));

        /* @var $response ResponseInterface */
        $response = $rateLimiter(new Request(), new Response());

        $this->assertEquals(RequestsPerWindowRateLimiter::LIMIT_EXCEEDED_HTTP_STATUS_CODE, $response->getStatusCode());

        sleep(3);

        /* @var $response ResponseInterface */
        $response = $rateLimiter(new Request(), new Response());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('1', $response->getHeaderLine(RequestsPerWindowRateLimiter::HEADER_REMAINING));
    }
}
