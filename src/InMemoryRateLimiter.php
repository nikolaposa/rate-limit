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
final class InMemoryRateLimiter extends AbstractRateLimiter
{
    /**
     * @var array
     */
    protected $store = [];

    protected function get(string $key, int $default) : int
    {
        if (
            !$this->has($key)
            || $this->hasExpired($key)
        ) {
            return $default;
        }

        return $this->store[$key]['current'];
    }

    protected function init(string $key)
    {
        $this->store[$key] = [
            'current' => 1,
            'expires' => time() + $this->window,
        ];
    }

    protected function increment(string $key)
    {
        $this->store[$key]['current']++;
    }

    protected function ttl(string $key) : float
    {
        if (!isset($this->store[$key])) {
            return 0;
        }

        return max($this->store[$key]['expires'] - time(), 0);
    }

    private function has(string $key) : bool
    {
        return array_key_exists($key, $this->store);
    }

    private function hasExpired(string $key) : bool
    {
        return time() > $this->store[$key]['expires'];
    }
}
