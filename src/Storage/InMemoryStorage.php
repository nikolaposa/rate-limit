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

/**
 * @author Nikola Posa <posa.nikola@gmail.com>
 */
final class InMemoryStorage implements StorageInterface
{
    /**
     * @var array
     */
    protected $store = [];

    /**
     * {@inheritdoc}
     */
    public function get(string $key, $default = false)
    {
        if (
            !$this->has($key)
            || $this->hasExpired($key)
        ) {
            return $default;
        }

        return $this->store[$key]['data'];
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, $value, int $ttl)
    {
        $this->store[$key] = [
            'data' => $value,
            'expires' => time() + $ttl,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function increment(string $key, int $by)
    {
        $this->store[$key]['data'] += $by;
    }

    /**
     * {@inheritdoc}
     */
    public function ttl(string $key) : int
    {
        if (!isset($this->store[$key]['expires'])) {
            return -1;
        }

        return max($this->store[$key]['expires'] - time(), 0);
    }

    private function has(string $key) : bool
    {
        return array_key_exists($key, $this->store);
    }

    private function hasExpired(string $key) : bool
    {
        if (!isset($this->store[$key]['expires'])) {
            return false;
        }

        return time() > $this->store[$key]['expires'];
    }
}
