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

namespace RateLimit;

use Psr\Http\Message\ServerRequestInterface;
use RateLimit\Exception\RateLimitExceededException;

/**
 * @author Nikola Posa <posa.nikola@gmail.com>
 */
interface RateLimiterInterface
{
    /**
     * @param ServerRequestInterface $serverRequest
     *
     * @throws RateLimitExceededException
     *
     * @return void
     */
    public function handle(ServerRequestInterface $serverRequest);
}
