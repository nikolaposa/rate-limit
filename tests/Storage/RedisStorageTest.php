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

namespace RateLimit\Tests\Storage;

use RateLimit\Storage\StorageInterface;
use RateLimit\Storage\RedisStorage;
use Redis;

/**
 * @author Nikola Posa <posa.nikola@gmail.com>
 */
class RedisStorageTest extends StorageTest
{
    /**
     * @var Redis
     */
    protected $redis;

    protected function getStorage() : StorageInterface
    {
        $this->redis = new Redis();

        $success = @ $this->redis->connect('127.0.0.1');

        if (!$success) {
            $this->markTestSkipped('Cannot connect to Redis.');
        }

        $this->redis->flushDB();

        return new RedisStorage($this->redis);
    }
}
