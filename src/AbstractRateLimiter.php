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

namespace RateLimit;

use RateLimit\Storage\StorageInterface;
use RateLimit\Identity\IdentityGeneratorInterface;

/**
 * @author Nikola Posa <posa.nikola@gmail.com>
 */
abstract class AbstractRateLimiter implements RateLimiterInterface
{
    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * @var IdentityGeneratorInterface
     */
    protected $identityGenerator;

    public function __construct(StorageInterface $storage, IdentityGeneratorInterface $identityGenerator)
    {
        $this->storage = $storage;
        $this->identityGenerator = $identityGenerator;
    }
}
