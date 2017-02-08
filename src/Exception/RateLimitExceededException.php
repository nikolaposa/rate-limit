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
use RateLimit\Status;

/**
 * @author Nikola Posa <posa.nikola@gmail.com>
 */
class RateLimitExceededException extends RuntimeException implements ExceptionInterface
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var Status
     */
    protected $status;

    public static function forKeyAndStatus(string $key, Status $status)
    {
        $exception = new static('Rate limit exceeded');

        $exception->key = $key;
        $exception->status = $status;

        return $exception;
    }

    public function getKey() : string
    {
        return $this->key;
    }

    public function getStatus() : Status
    {
        return $this->status;
    }
}
