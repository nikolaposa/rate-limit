<?php
/**
 * Implementation of nikolaposa/rate-limit to allow using a PSR-16 cache for storage.
 *
 * @see https://www.php-fig.org/psr/psr-16/
 *
 * @license    MIT
 * @author     BrianHenryIE <BrianHenryIE@gmail.com>
 */

declare(strict_types=1);

namespace RateLimit;

use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use RateLimit\Exception\LimitExceeded;
use function time;

class Psr16RateLimiter implements RateLimiter, SilentRateLimiter
{

    /** @var CacheInterface */
    protected $psrCache;

    /** @var string */
    protected $keyPrefix;

    public function __construct(CacheInterface $psrCache, string $keyPrefix = '')
    {
        $this->keyPrefix = $keyPrefix;
        $this->psrCache  = $psrCache;
    }

    public function limit(string $identifier, Rate $rate): void
    {
        $key = $this->key($identifier, $rate->getInterval());

        $current = $this->getCurrentCount($key);

        if ($current >= $rate->getOperations()) {
            throw LimitExceeded::for($identifier, $rate);
        }

        $this->updateCounter($key, $rate->getInterval());
    }

    public function limitSilently(string $identifier, Rate $rate): Status
    {
        $key = $this->key($identifier, $rate->getInterval());

        $current = $this->updateCounter($key, $rate->getInterval());

        return Status::from(
            $identifier,
            $current,
            $rate->getOperations(),
            time() + $rate->getInterval()
        );
    }

    /**
     * The key includes the interval so multiple intervals:incidents can be counted against the one identifier.
     * e.g. it can happen five times in one minute but no more then ten times in one hour.
     *
     * @param string $identifier
     * @param int    $interval
     * @return string
     */
    protected function key(string $identifier, int $interval): string
    {
        return "{$this->keyPrefix}{$identifier}:$interval";
    }

    /**
     * Return a count of unexpired records for the key.
     *
     * @param string $key
     * @return int
     */
    protected function getCurrentCount(string $key): int
    {
        $stored_values = $this->getCurrentStoredCounter($key);

        return count($stored_values);
    }

    /**
     * @param string $key
     * @return array<int, array{key: string, created_at: int, expires_at: int, interval :int}>
     */
    protected function getCurrentStoredCounter(string $key): array
    {
        try {
            $stored_values = $this->psrCache->get($key, []);
        } catch (InvalidArgumentException $e) {
            $stored_values = [];
        }

        foreach ($stored_values as $created_time => $value) {
            if (isset($value['expires_at']) && $value['expires_at'] < time()) {
                unset($stored_values[ $created_time ]);
            }
        }

        return $stored_values;
    }

    protected function updateCounter(string $key, int $interval): int
    {
        $stored_values = $this->getCurrentStoredCounter($key);

        $created_time = time();
        $expires_at   = $created_time + $interval;

        $stored_values[ $created_time ] = [
            'key'          => $key,
            'created_time' => $created_time,
            'expires_at'   => $expires_at,
            'interval'     => $interval,
        ];

        $this->psrCache->set($key, $stored_values, $interval);

        return count($stored_values);
    }
}
