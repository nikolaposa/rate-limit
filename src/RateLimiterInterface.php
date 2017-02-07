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

/**
 * @author Nikola Posa <posa.nikola@gmail.com>
 */
interface RateLimiterInterface
{
    /**
     * @param string $identity
     *
     * @return RateLimit
     */
    public function hit(string $identity);
}
