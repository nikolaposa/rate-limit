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

use PHPUnit\Framework\TestCase;
use RateLimit\Storage\StorageInterface;
use RateLimit\Storage\RedisStorage;
use Redis;

/**
 * @author Nikola Posa <posa.nikola@gmail.com>
 */
class RedisStorageTest extends TestCase
{
    /**
     * @var StorageInterface
     */
    protected $storage;

    protected function setUp()
    {
        $redis = new Redis();

        $success = @ $redis->connect('127.0.0.1');

        if (!$success) {
            $this->markTestSkipped('Cannot connect to Redis.');
        }

        $this->storage = new RedisStorage($redis);
    }

    /**
     * @test
     */
    public function it_sets_value_under_key()
    {
        $this->storage->set('key1', 'value', 3600);

        $this->assertEquals('value', $this->storage->get('key1'));
    }

    /**
     * @test
     */
    public function it_returns_default_value_if_key_not_set()
    {
        $this->assertEquals('default', $this->storage->get('not_set', 'default'));
    }

    /**
     * @test
     */
    public function it_increments_key()
    {
        $this->storage->set('increment_key', 10, 3600);

        $this->storage->increment('increment_key', 5);

        $this->assertEquals(15, $this->storage->get('increment_key'));
    }

    /**
     * @test
     */
    public function it_gets_ttl_for_a_key()
    {
        $this->storage->set('ttl_key', 'foo', 3600);

        $this->assertEquals(3600, $this->storage->ttl('ttl_key'));
    }
}
