<?php

declare(strict_types=1);

namespace RateLimit\Http;

use Psr\Http\Message\ServerRequestInterface;

final class ResolveIdentifierFromIpAddress implements ResolveIdentifier
{
    public function fromRequest(ServerRequestInterface $request): string
    {
        $serverParams = $request->getServerParams();

        if (array_key_exists('HTTP_CLIENT_IP', $serverParams)) {
            return $serverParams['HTTP_CLIENT_IP'];
        }

        if (array_key_exists('HTTP_X_FORWARDED_FOR', $serverParams)) {
            return $serverParams['HTTP_X_FORWARDED_FOR'];
        }

        return $serverParams['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}
