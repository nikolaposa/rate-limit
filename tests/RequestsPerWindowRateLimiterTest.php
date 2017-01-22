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
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response;

/**
 * @author Nikola Posa <posa.nikola@gmail.com>
 */
class RequestsPerWindowRateLimiterTest extends TestCase
{
    /**
     * @test
     */
    public function it_sets_appropriate_response_status_when_limit_is_reached()
    {
        $rateLimiter = RequestsPerWindowRateLimiter::create(new InMemoryStorage(), [
            'limit' => 1,
            'window' => 3600,
        ]);

        $rateLimiter(
            ServerRequestFactory::fromGlobals([
                'HTTP_CLIENT_IP' => '192.168.1.7',
            ]),
            new Response()
        );

        /* @var $response ResponseInterface */
        $response = $rateLimiter(
            ServerRequestFactory::fromGlobals([
                'HTTP_CLIENT_IP' => '192.168.1.7',
            ]),
            new Response()
        );

        $this->assertEquals(RequestsPerWindowRateLimiter::LIMIT_EXCEEDED_HTTP_STATUS_CODE, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_sets_appropriate_headers_when_limit_is_reached()
    {
        $rateLimiter = RequestsPerWindowRateLimiter::create(new InMemoryStorage(), [
            'limit' => 1,
            'window' => 3600,
        ]);

        $rateLimiter(
            ServerRequestFactory::fromGlobals([
                'HTTP_CLIENT_IP' => '192.168.1.7',
            ]),
            new Response()
        );

        /* @var $response ResponseInterface */
        $response = $rateLimiter(
            ServerRequestFactory::fromGlobals([
                'HTTP_CLIENT_IP' => '192.168.1.7',
            ]),
            new Response()
        );

        $this->assertEquals('1', $response->getHeaderLine(RequestsPerWindowRateLimiter::HEADER_LIMIT));
        $this->assertEquals('0', $response->getHeaderLine(RequestsPerWindowRateLimiter::HEADER_REMAINING));
        $this->assertTrue($response->hasHeader(RequestsPerWindowRateLimiter::HEADER_LIMIT));
    }
}
