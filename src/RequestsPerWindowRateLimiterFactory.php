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

use RateLimit\Options\RequestsPerWindowOptions;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RateLimit\Identity\IpAddressIdentityGenerator;
use RateLimit\Storage\InMemoryStorage;
use RateLimit\Storage\FilesystemStorage;

/**
 * @author Nikola Posa <posa.nikola@gmail.com>
 */
final class RequestsPerWindowRateLimiterFactory
{
    const DEFAULT_LIMIT = 100;
    const DEFAULT_WINDOW = 15 * 60;

    public static function createInMemoryRateLimiter(array $options = []) : RequestsPerWindowRateLimiter
    {
        return new RequestsPerWindowRateLimiter(
            new InMemoryStorage(),
            new IpAddressIdentityGenerator(),
            self::createOptions($options)
        );
    }

    public static function createFilesystemBackedRateLimiter(string $storageDirectory, array $options = []) : RequestsPerWindowRateLimiter
    {
        return new RequestsPerWindowRateLimiter(
            new FilesystemStorage($storageDirectory),
            new IpAddressIdentityGenerator(),
            self::createOptions($options)
        );
    }

    public static function createOptions(array $options = []) : RequestsPerWindowOptions
    {
        $options = array_merge(self::getDefaultOptions(), $options);

        return new RequestsPerWindowOptions(
            $options['limit'],
            $options['window'],
            $options['limitExceededHandler']
        );
    }
    private static function getDefaultOptions() : array
    {
        return [
            'limit' => self::DEFAULT_LIMIT,
            'window' => self::DEFAULT_WINDOW,
            'limitExceededHandler' => function (RequestInterface $request, ResponseInterface $response) {
                return $response;
            },
        ];
    }
}
