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
interface StorageInterface
{
    /**
     * @param string $key
     *
     * @throws StorageRecordNotExistException
     *
     * @return mixed
     */
    public function get(string $key);

    /**
     * @param string $key
     * @param mixed $data
     *
     * @return void
     */
    public function set(string $key, $data);
}
