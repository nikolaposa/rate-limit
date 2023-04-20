<?php

declare(strict_types=1);

namespace RateLimit\Tests;

use Psr\SimpleCache\CacheInterface;
use RateLimit\Exception\CannotUseRateLimiter;
use RateLimit\Psr16RateLimiter;
use RateLimit\Rate;
use RateLimit\RateLimiter;

class Psr16RateLimiterTest extends RateLimiterTest
{
    protected function getRateLimiter(Rate $rate): RateLimiter
    {
        $cacheInterface = new class() implements CacheInterface {

            /** @var array */
            protected $cache = [];

            /**
             * @param string $key
             * @param mixed|null $default
             * @return ?mixed
             */
            public function get($key, $default = null)
            {
                if (!isset($this->cache[$key])) {
                    return $default;
                }

                if ($this->cache[$key]['expires'] < time()) {
                    return $default;
                }

                return $this->cache[$key]['value'];
            }

            /**
             * @param string $key
             * @param mixed $value
             * @param ?int $ttl
             */
            public function set($key, $value, $ttl = null)
            {
                if (!isset($this->cache[$key])) {
                    $this->cache[$key] = [];
                }
                $this->cache[$key]['expires'] = time() + $ttl;
                $this->cache[$key]['value'] = $value;

                return true;
            }

            public function delete($key)
            {
                // Not used.
                return false;
            }

            public function clear()
            {
                // Not used.
                return false;
            }

            public function getMultiple($keys, $default = null)
            {
                // Not used.
                return [];
            }

            public function setMultiple($values, $ttl = null)
            {
                // Not used.
                return false;
            }

            public function deleteMultiple($keys): bool
            {
                // Not used.
                return false;
            }

            public function has($key)
            {
                // Not used.
                return false;
            }
        };

        try {
            $rateLimiter = $this->getRateLimiterFromCache($rate, $cacheInterface);
        } catch (CannotUseRateLimiter $exception) {
            $this->markTestSkipped($exception->getMessage());
        }

        return $rateLimiter;
    }

    protected function getRateLimiterFromCache(Rate $rate, CacheInterface $cacheInterface): RateLimiter
    {
        return new Psr16RateLimiter($rate, $cacheInterface);
    }

    /**
     * @test
     *
     * @covers \RateLimit\Psr16RateLimiter::getCurrentStoredCounter
     */
    public function it_handles_bool_returned_from_cache(): void
    {
        $cacheInterface = new class() implements CacheInterface {

            /** @var array */
            protected $cache = [];

            /**
             * @param string $key
             * @param mixed|null $default
             * @return ?mixed
             */
            public function get($key, $default = null)
            {
                return false;
            }

            /**
             * @param string $key
             * @param mixed $value
             * @param ?int $ttl
             */
            public function set($key, $value, $ttl = null)
            {
                if (!isset($this->cache[$key])) {
                    $this->cache[$key] = [];
                }
                $this->cache[$key]['expires'] = time() + $ttl;
                $this->cache[$key]['value'] = $value;

                return true;
            }

            public function delete($key)
            {
                // Not used.
                return false;
            }

            public function clear()
            {
                // Not used.
                return false;
            }

            public function getMultiple($keys, $default = null)
            {
                // Not used.
                return [];
            }

            public function setMultiple($values, $ttl = null)
            {
                // Not used.
                return false;
            }

            public function deleteMultiple($keys): bool
            {
                // Not used.
                return false;
            }

            public function has($key)
            {
                // Not used.
                return false;
            }
        };

        $rateLimiter = $this->getRateLimiterFromCache(Rate::perSecond(1), $cacheInterface);

        $identifier = 'test';

        try {
            $rateLimiter->limit($identifier);
        } catch (\Throwable $error) {
            // Invalid argument supplied for foreach()
            self::fail($error->getMessage());
        }

        self::expectNotToPerformAssertions();
    }
}
