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
abstract class AbstractRateLimiter implements RateLimiterInterface
{
    /**
     * @var int
     */
    protected $limit;

    /**
     * @var int
     */
    protected $window;

    /**
     * @var string
     */
    protected $key;

    public function __construct(int $limit, int $window)
    {
        $this->limit = $limit;
        $this->window = $window;
    }

    /**
     * {@inheritdoc}
     */
    public function getLimit() : int
    {
        return $this->limit;
    }

    /**
     * {@inheritdoc}
     */
    public function getWindow() : int
    {
        return $this->window;
    }

    /**
     * {@inheritdoc}
     */
    public function hit(string $key)
    {
        $current = $this->getCurrent($key);

        if ($current >= $this->limit) {
            throw RateLimitExceededException::forKey($key);
        }

        if (0 === $current) {
            $this->init($key);
            return;
        }

        $this->increment($key);
    }

    /**
     * {@inheritdoc}
     */
    public function getRemainingAttempts(string $key) : int
    {
        return max(0, $this->limit - $this->getCurrent($key));
    }

    /**
     * {@inheritdoc}
     */
    public function getResetAt(string $key) : int
    {
        return (int)ceil(microtime(true) + $this->ttl($key));
    }

    protected function getCurrent(string $key) : int
    {
        return $this->get($key, 0);
    }

    abstract protected function get(string $key, int $default) : int;

    abstract protected function init(string $key);

    abstract protected function increment(string $key);

    abstract protected function ttl(string $key) : float;
}
