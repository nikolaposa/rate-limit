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

namespace RateLimit\Storage;

use Redis;
use RateLimit\Exception\StorageValueNotFoundException;

/**
 * @author Nikola Posa <posa.nikola@gmail.com>
 */
final class RedisStorage implements StorageInterface
{
    /**
     * @var Redis
     */
    private $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key)
    {
        $value = $this->redis->get($key);

        if (false === $value) {
            throw StorageValueNotFoundException::forKey($key);
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, $value, int $ttl)
    {
        $this->redis->setex($key, $ttl, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function increment(string $key, int $by)
    {
        $this->redis->incrBy($key, $by);
    }

    /**
     * {@inheritdoc}
     */
    public function ttl(string $key) : int
    {
        $ttl = $this->redis->ttl($key);

        if (false === $ttl || $ttl < 0) {
            return -1;
        }

        return $ttl;
    }
}
