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

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Nikola Posa <posa.nikola@gmail.com>
 */
interface RateLimiterInterface
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param callable $out
     *
     * @return ResponseInterface|null
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $out = null);
}
