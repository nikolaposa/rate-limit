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

namespace RateLimit\Exception;

use RuntimeException;

/**
 * @author Nikola Posa <posa.nikola@gmail.com>
 */
class RequestsLimitExceededException extends RuntimeException implements RateLimitExceededException
{
    public static function forRequestsLimitAndWindow(int $requestsLimit, int $window)
    {
        return new self(sprintf(
            'Limit of %s request(s) per %s minute(s) has been exceeded',
            $requestsLimit,
            round((int) $window / 60)
        ));
    }
}
