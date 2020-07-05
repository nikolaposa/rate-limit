<?php

declare(strict_types=1);

namespace RateLimit\Exception;

use RuntimeException;

final class CannotUseRateLimiter extends RuntimeException implements RateLimitException
{
}
