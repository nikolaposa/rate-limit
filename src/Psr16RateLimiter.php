<?php

/**
 * Implementation of nikolaposa/rate-limit to allow using a PSR-16 cache for storage.
 *
 * @see https://www.php-fig.org/psr/psr-16/
 *
 * @license    MIT
 * @author     BrianHenryIE <BrianHenryIE@gmail.com>
 * @author     Денис Попов <karneds@gmail.com>
 */

declare(strict_types=1);

namespace RateLimit;

use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use RateLimit\Exception\LimitExceeded;
use function time;

class Psr16RateLimiter extends ConfigurableRateLimiter implements RateLimiter, SilentRateLimiter
{

    /** @var CacheInterface */
    protected $psrCache;

    /** @var string */
    protected $keyPrefix;

    public function __construct(Rate $rate, CacheInterface $cache, string $keyPrefix = '')
    {
        parent::__construct($rate);
        $this->psrCache = $cache;
        $this->keyPrefix = $keyPrefix;
    }


    public function limit(string $identifier): void
    {
        $key = $this->key($identifier);

        $current = $this->getCurrentCount($key);

        if ($current >= $this->rate->getOperations()) {
            throw LimitExceeded::for($identifier, $this->rate);
        }

        $this->updateCounter($key);
    }

    public function limitSilently(string $identifier): Status
    {
        $key = $this->key($identifier);

        $current = $this->updateCounter($key);

        return Status::from(
            $identifier,
            $current,
            $this->rate->getOperations(),
            time() + $this->rate->getInterval()
        );
    }

    /**
     * The key includes the interval so multiple intervals:incidents can be counted against the one identifier.
     * e.g. it can happen five times in one minute but no more then ten times in one hour.
     *
     * @param string $identifier
     * @return string
     */
    protected function key(string $identifier): string
    {
        return "{$this->keyPrefix}{$identifier}:{$this->rate->getInterval()}";
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
            $stored_values = $this->psrCache->get($key, []) ?: [];
        } catch (InvalidArgumentException $e) {
            $stored_values = [];
        }

        foreach ($stored_values as $created_time => $value) {
            if (isset($value['expires_at']) && $value['expires_at'] < time()) {
                unset($stored_values[$created_time]);
            }
        }

        return $stored_values;
    }

    protected function updateCounter(string $key): int
    {
        $stored_values = $this->getCurrentStoredCounter($key);

        $created_time = time();
        $interval = $this->rate->getInterval();
        $expires_at = $created_time + $interval;
        $stored_values[] = [
            'key' => $key,
            'created_time' => $created_time,
            'expires_at' => $expires_at,
            'interval' => $interval,
        ];

        $this->psrCache->set($key, $stored_values, $interval);

        return  $this->getCurrentCount($key);
    }
}
