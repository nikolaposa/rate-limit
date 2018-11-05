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

use Redis;

/**
 * @author Nikola Posa <posa.nikola@gmail.com>
 */
final class RedisRateLimiter extends AbstractRateLimiter
{
    /**
     * @var Redis
     */
    private $redis;

    public function __construct(Redis $redis, int $limit, int $window)
    {
        $this->redis = $redis;

        parent::__construct($limit, $window);
    }

    protected function get(string $key, int $default) : int
    {
        $value = $this->redis->get($key);

        if (false === $value) {
            return $default;
        }

        return (int) $value;
    }

    protected function init(string $key)
    {
        $this->redis->setex($key, $this->window, 1);
    }

    protected function increment(string $key)
    {
        $this->redis->incr($key);
    }

    protected function ttl(string $key) : float
    {
        $ttl = $this->redis->pttl($key);
        if ( $ttl===-1 ) {
            // The key was created without an expiration date
            $this->redis->expire($key, $this->window);
            $ttl = $this->window*1000;
        }

        return max($ttl / 1000, 0.0);
    }
}
