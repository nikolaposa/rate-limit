<?php

declare(strict_types=1);

namespace RateLimit\Http;

use Psr\Http\Message\ServerRequestInterface;

interface ResolveIdentifier
{
    public function fromRequest(ServerRequestInterface $request): string;
}
