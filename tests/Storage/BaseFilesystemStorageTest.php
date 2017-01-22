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
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * @author Nikola Posa <posa.nikola@gmail.com>
 */
abstract class BaseFilesystemStorageTest extends TestCase
{
    /**
     * @var string
     */
    protected $directory;

    protected function setUp()
    {
        $this->createTestDirectory();
    }

    protected function tearDown()
    {
        $this->removeTestDirectory();
    }
    
    final protected function createTestDirectory()
    {
        do {
            $this->directory = sys_get_temp_dir() . '/rate_limit_storage_' . uniqid();
        } while (file_exists($this->directory));
    }
    
    final protected function removeTestDirectory()
    {
        if (!is_dir($this->directory)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->directory),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile()) {
                @unlink($fileInfo->getRealPath());
            } elseif ($fileInfo->isDir()) {
                @rmdir($fileInfo->getRealPath());
            }
        }

        @rmdir($this->directory);
    }
}
