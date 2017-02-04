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

namespace RateLimit\Exception;

use RuntimeException;

/**
 * @author Nikola Posa <posa.nikola@gmail.com>
 */
class StorageValueNotFoundException extends RuntimeException implements ExceptionInterface
{
    public static function forKey(string $key)
    {
        return new self(sprintf(
            "'%s' was not found in storage",
            $key
        ));
    }
}
