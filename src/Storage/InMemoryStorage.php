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
class InMemoryStorage implements StorageInterface
{
    /**
     * @var array
     */
    protected $store = [];

    /**
     * {@inheritdoc}
     */
    public function get(string $key)
    {
        if (!array_key_exists($key, $this->store)) {
            throw StorageRecordNotExistException::forKey($key);
        }

        return $this->store[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, $data)
    {
        $this->store[$key] = $data;
    }
}
