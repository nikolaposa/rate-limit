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

use RateLimit\Exception\StorageValueNotFoundException;

/**
 * @author Nikola Posa <posa.nikola@gmail.com>
 */
interface StorageInterface
{
    /**
     * @param string $key
     *
     * @throws StorageValueNotFoundException
     *
     * @return mixed
     */
    public function get(string $key);

    /**
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     *
     * @return void
     */
    public function set(string $key, $value, int $ttl);

    /**
     * @param string $key
     * @param int $by
     *
     * @return void
     */
    public function increment(string $key, int $by);

    /**
     * @param string $key
     *
     * @return int
     */
    public function ttl(string $key) : int;
}
