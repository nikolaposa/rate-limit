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

use RateLimit\Exception\RateLimitExceededException;

/**
 * @author Nikola Posa <posa.nikola@gmail.com>
 */
interface RateLimiterInterface
{
    /**
     * @param int $limit
     *
     * @return void
     */
    public function setLimit(int $limit);

    /**
     * @return int
     */
    public function getLimit() : int;

    /**
     * @return int
     */
    public function getWindow() : int;

    /**
     * @param string $key
     *
     * @throws RateLimitExceededException
     *
     * @return void
     */
    public function hit(string $key);

    /**
     * @param string $key
     *
     * @return int
     */
    public function getRemainingAttempts(string $key) : int;

    /**
     * @param string $key
     *
     * @return int Timestamp in the future
     */
    public function getResetAt(string $key) : int;
}
