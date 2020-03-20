<?php

declare(strict_types=1);

namespace RateLimit\Http;

use Psr\Http\Message\ServerRequestInterface;
use RateLimit\QuotaPolicy;

interface GetQuotaPolicy
{
    public function forRequest(ServerRequestInterface $request): ?QuotaPolicy;
}
