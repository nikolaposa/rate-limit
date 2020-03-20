<?php

declare(strict_types=1);

namespace RateLimit\Tests\Http;

use Laminas\Diactoros\ServerRequestFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use RateLimit\Http\ResolveIdentifier;
use RateLimit\Http\ResolveIdentifierFromIpAddress;

class ResolveIdentifierFromIpAddressTest extends TestCase
{
    /** @var ResolveIdentifier */
    protected $resolveIdentifier;

    protected function setUp(): void
    {
        $this->resolveIdentifier = new ResolveIdentifierFromIpAddress();
    }

    /**
     * @test
     */
    public function it_resolves_http_client_ip_as_identifier(): void
    {
        $request = ServerRequestFactory::fromGlobals([
            'HTTP_CLIENT_IP' => '192.168.1.7',
        ]);

        $identifier = $this->resolveIdentifier->fromRequest($request);

        $this->assertSame('192.168.1.7', $identifier);
    }

    /**
     * @test
     */
    public function it_resolves_http_x_forwarded_for_as_identifier(): void
    {
        $request = ServerRequestFactory::fromGlobals([
            'HTTP_X_FORWARDED_FOR' => '192.168.1.7',
        ]);

        $identifier = $this->resolveIdentifier->fromRequest($request);

        $this->assertSame('192.168.1.7', $identifier);
    }

    /**
     * @test
     */
    public function it_resolves_remote_addr_as_identifier(): void
    {
        $request = ServerRequestFactory::fromGlobals([
            'REMOTE_ADDR' => '192.168.1.7',
        ]);

        $identifier = $this->resolveIdentifier->fromRequest($request);

        $this->assertSame('192.168.1.7', $identifier);
    }

    /**
     * @test
     */
    public function it_resolves_default_identifier_if_none_of_related_server_params_is_not_set(): void
    {
        $request = ServerRequestFactory::fromGlobals([]);

        $identifier = $this->resolveIdentifier->fromRequest($request);

        $this->assertSame('127.0.0.1', $identifier);
    }

    /**
     * @test
     *
     * @dataProvider getServerRequests
     */
    public function it_resolves_identity_based_on_correct_server_params_priority(ServerRequestInterface $request, string $expectedIdentity): void
    {
        $identifier = $this->resolveIdentifier->fromRequest($request);

        $this->assertSame($expectedIdentity, $identifier);
    }

    public function getServerRequests(): array
    {
        return [
            'http_client_ip_first' => [
                ServerRequestFactory::fromGlobals([
                    'REMOTE_ADDR' => '192.168.1.5',
                    'HTTP_X_FORWARDED_FOR' => '192.168.1.6',
                    'HTTP_CLIENT_IP' => '192.168.1.7',
                ]),
                '192.168.1.7'
            ],
            'x_forwarded_for_before_remote_addr' => [
                ServerRequestFactory::fromGlobals([
                    'REMOTE_ADDR' => '192.168.1.5',
                    'HTTP_X_FORWARDED_FOR' => '192.168.1.6',
                ]),
                '192.168.1.6'
            ],
        ];
    }
}
