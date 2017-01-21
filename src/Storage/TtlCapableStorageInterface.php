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

/**
 * @author Nikola Posa <posa.nikola@gmail.com>
 */
interface TtlCapableStorageInterface
{
    /**
     * @param string $key
     * @param $data
     * @param int $ttl Lifetime in number of seconds
     *
     * @return void
     */
    public function setTemporary(string $key, $data, int $ttl);
}
