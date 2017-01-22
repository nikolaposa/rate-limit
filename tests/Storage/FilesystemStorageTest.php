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
use RateLimit\Storage\FilesystemStorage;
use RateLimit\Exception\StorageRecordNotExistException;

/**
 * @author Nikola Posa <posa.nikola@gmail.com>
 */
class FilesystemStorageTest extends BaseFilesystemStorageTest
{
    /**
     * @var StorageInterface
     */
    protected $storage;

    protected function setUp()
    {
        parent::setUp();

        $this->storage = new FilesystemStorage($this->directory);
    }

    /**
     * @test
     */
    public function it_saves_data_under_key()
    {
        $this->storage->set('test', ['foo' => 'bar']);

        $this->assertNotEmpty(scandir($this->directory));
    }

    /**
     * @test
     */
    public function it_gets_previously_set_data()
    {
        $data = ['foo' => 'bar'];

        $this->storage->set('test', ['foo' => 'bar']);

        $this->assertSame($data, $this->storage->get('test'));
    }

    /**
     * @test
     */
    public function it_raises_exception_if_key_not_exists()
    {
        $this->expectException(StorageRecordNotExistException::class);

        $this->storage->get('invalid');
    }
}
