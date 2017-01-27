<?php
/**
 * This file is part of the Rate Limit package.
 *
 * Copyright (c) Nikola Posa
 *
 * For full copyright and license information, please refer to the LICENSE file,
 * located at the package root folder.
 */

namespace RateLimit\Tests\Storage;

use PHPUnit_Framework_TestCase;
use RateLimit\Storage\StorageInterface;

/**
 * @author Nikola Posa <posa.nikola@gmail.com>
 */
abstract class StorageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var StorageInterface
     */
    protected $storage;

    protected function setUp()
    {
        $this->storage = $this->getStorage();
    }

    abstract protected function getStorage() : StorageInterface;

    /**
     * @test
     */
    public function it_sets_value_under_key()
    {
        $this->storage->set('key1', 'test', 3600);

        $this->assertEquals('test', $this->storage->get('key1'));
    }

    /**
     * @test
     */
    public function it_gets_default_value_if_key_not_set()
    {
        $this->assertEquals('default', $this->storage->get('not_set', 'default'));
    }

    /**
     * @test
     */
    public function it_gets_default_value_if_key_has_expired()
    {
        $this->storage->set('expired_key', 'test', 1);

        sleep(2);

        $this->assertEquals('default', $this->storage->get('expired_key', 'default'));
    }

    /**
     * @test
     */
    public function it_increments_key()
    {
        $this->storage->set('increment_key', 10, 3600);

        $this->storage->increment('increment_key', 5);

        $this->assertEquals(15, (int) $this->storage->get('increment_key'));
    }

    /**
     * @test
     */
    public function it_increments_not_previously_set_key()
    {
        $this->storage->increment('increment_non_existing_key', 5);

        $this->assertEquals(5, (int) $this->storage->get('increment_non_existing_key'));
    }

    /**
     * @test
     */
    public function it_gets_ttl_for_a_key()
    {
        $this->storage->set('ttl_key', 'test', 3600);

        $this->assertEquals(3600, $this->storage->ttl('ttl_key'));
    }

    /**
     * @test
     */
    public function it_gets_no_ttl_if_key_not_set()
    {
        $this->assertEquals(-1, $this->storage->ttl('ttl_key_not_set'));
    }
}
