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
use RateLimit\Storage\InMemoryStorage;

/**
 * @author Nikola Posa <posa.nikola@gmail.com>
 */
class InMemoryStorageTest extends StorageTest
{
    protected function getStorage() : StorageInterface
    {
        return new InMemoryStorage();
    }
}
