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
    protected $identity;

    public function __construct(StorageInterface $storage, int $limit, int $window)
    {
        $this->storage = $storage;
        $this->limit = $limit;
        $this->window = $window;
    }

    /**
     * @param string $identity
     *
     * @return RateLimit
     */
    public function hit(string $identity)
    {
        $this->identity = $identity;

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
        return $this->storage->get($this->identity);
    }

    private function init()
    {
        $this->storage->set($this->identity, 1, $this->window);
    }

    private function increment()
    {
        $this->storage->increment($this->identity, 1);
    }

    private function createRateLimit(int $current) : RateLimit
    {
        return new RateLimit(
            $this->identity,
            $this->limit,
            $current,
            $this->storage->ttl($this->identity)
        );
    }
}
