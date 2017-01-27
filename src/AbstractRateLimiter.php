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
use RateLimit\Identity\IdentityResolverInterface;

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
     * @var IdentityResolverInterface
     */
    protected $identityResolver;

    public function __construct(StorageInterface $storage, IdentityResolverInterface $identityResolver)
    {
        $this->storage = $storage;
        $this->identityResolver = $identityResolver;
    }
}
