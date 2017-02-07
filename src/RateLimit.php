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
class RateLimit
{
    /**
     * @var string
     */
    protected $identity;

    /**
     * @var int
     */
    protected $limit;

    /**
     * @var int
     */
    protected $current;

    /**
     * @var int
     */
    protected $ttl;

    public function __construct(string $identity, int $limit, int $current, int $ttl)
    {
        $this->identity = $identity;
        $this->limit = $limit;
        $this->current = $current;
        $this->ttl = $ttl;
    }

    public function getIdentity() : string
    {
        return $this->identity;
    }

    public function getLimit() : int
    {
        return $this->limit;
    }

    public function getRemainingAttempts() : int
    {
        return max(0, $this->limit - $this->current);
    }

    public function getResetAt() : int
    {
        return time() + $this->ttl;
    }

    public function isExceeded() : bool
    {
        return ($this->current > $this->limit);
    }
}
