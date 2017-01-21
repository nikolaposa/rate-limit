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
use Zend\Diactoros\ServerRequestFactory;
use RateLimit\Exception\RateLimitExceededException;

/**
 * @author Nikola Posa <posa.nikola@gmail.com>
 */
class RequestsPerWindowRateLimiterTest extends TestCase
{
    /**
     * @test
     */
    public function it_raises_exception_when_limit_is_reached()
    {
        $rateLimiter = RequestsPerWindowRateLimiter::create(new InMemoryStorage(), [
            'limit' => 1,
            'window' => 3600,
        ]);

        $rateLimiter->handle(ServerRequestFactory::fromGlobals([
            'HTTP_CLIENT_IP' => '192.168.1.7',
        ]));
        
        $this->expectException(RateLimitExceededException::class);

        $rateLimiter->handle(ServerRequestFactory::fromGlobals([
            'HTTP_CLIENT_IP' => '192.168.1.7',
        ]));
    }
}
