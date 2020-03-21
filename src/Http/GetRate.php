<?php

declare(strict_types=1);

namespace RateLimit\Http;

use Psr\Http\Message\ServerRequestInterface;
use RateLimit\Rate;

interface GetRate
{
    public function forRequest(ServerRequestInterface $request): ?Rate;
}
