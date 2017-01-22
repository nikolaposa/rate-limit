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

use RateLimit\Exception\StorageRecordNotExistException;

/**
 * @author Nikola Posa <posa.nikola@gmail.com>
 */
final class FilesystemStorage implements StorageInterface
{
    /**
     * @var string
     */
    private $directory;

    public function __construct(string $directory)
    {
        $this->directory = rtrim($directory, DIRECTORY_SEPARATOR);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key)
    {
        $filePath = $this->buildFilePath($key);

        if (!is_file($filePath)) {
            throw StorageRecordNotExistException::forKey($key);
        }

        $contents = file_get_contents($filePath);

        return unserialize($contents);
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, $data)
    {
        if (!is_dir($this->directory)) {
            mkdir($this->directory, 0777, true);
        }

        $filePath = $this->buildFilePath($key);

        $contents = serialize($data);

        file_put_contents($filePath, $contents, FILE_APPEND | LOCK_EX);
    }

    private function buildFilePath(string $key)
    {
        return $this->directory . DIRECTORY_SEPARATOR . md5($key);
    }
}
