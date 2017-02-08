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

use RateLimit\Storage\StorageInterface;
use RateLimit\Exception\StorageValueNotFoundException;

/**
 * @author Nikola Posa <posa.nikola@gmail.com>
 */
class DefaultRateLimiter implements RateLimiterInterface
{
    /**
     * @var StorageInterface
     */
    protected $storage;

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

    public function __construct(StorageInterface $storage, int $limit, int $window)
    {
        $this->storage = $storage;
        $this->limit = $limit;
        $this->window = $window;
    }

    /**
     * @param string $key
     *
     * @return RateLimit
     */
    public function hit(string $key)
    {
        $this->key = $key;

        try {
            $current = $this->getCurrent();

            $this->increment();
        } catch (StorageValueNotFoundException $ex) {
            $current = 1;
            $this->init();
        }

        return $this->createRateLimit($current);
    }

    private function getCurrent() : int
    {
        return $this->storage->get($this->key);
    }

    private function init()
    {
        $this->storage->set($this->key, 1, $this->window);
    }

    private function increment()
    {
        $this->storage->increment($this->key, 1);
    }

    private function createRateLimit(int $current) : RateLimit
    {
        return new RateLimit(
            $this->limit,
            $current,
            $this->storage->ttl($this->key)
        );
    }
}
