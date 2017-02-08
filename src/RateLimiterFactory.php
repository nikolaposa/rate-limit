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

use Redis;

/**
 * @author Nikola Posa <posa.nikola@gmail.com>
 */
final class RateLimiterFactory
{
    const DEFAULT_LIMIT = 100;
    const DEFAULT_WINDOW = 15 * 60;

    public static function createInMemoryRateLimiter(int $limit = self::DEFAULT_LIMIT, int $window = self::DEFAULT_WINDOW) : RateLimiterInterface
    {
        return new InMemoryRateLimiter($limit, $window);
    }

    public static function createRedisBackedRateLimiter(array $redisOptions = [], int $limit = self::DEFAULT_LIMIT, int $window = self::DEFAULT_WINDOW) : RateLimiterInterface
    {
        $redisOptions = array_merge([
            'host' => '127.0.0.1',
            'port' => 6379,
            'timeout' => 0.0,
        ], $redisOptions);

        $redis = new Redis();

        $redis->connect($redisOptions['host'], $redisOptions['port'], $redisOptions['timeout']);

        return new RedisRateLimiter($redis, $limit, $window);
    }
}
