# Rate Limit

[![Build Status](https://travis-ci.com/nikolaposa/rate-limit.svg?branch=master)](https://travis-ci.com/nikolaposa/rate-limit)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nikolaposa/rate-limit/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/nikolaposa/rate-limit/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/nikolaposa/rate-limit/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/nikolaposa/rate-limit/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/nikolaposa/rate-limit/v/stable)](https://packagist.org/packages/nikolaposa/rate-limit)
[![PDS Skeleton](https://img.shields.io/badge/pds-skeleton-blue.svg)](https://github.com/php-pds/skeleton)


General purpose rate limiter that can be used to limit the rate at which certain operation can be performed. Default implementation uses Redis as backend.
 
## Installation

The preferred method of installation is via [Composer](http://getcomposer.org/). Run the following
command to install the latest version of a package and add it to your project's `composer.json`:

```bash
composer require nikolaposa/rate-limit
```

## Usage

**Offensive rate limiting**

```php
use RateLimit\Exception\LimitExceeded;
use RateLimit\Rate;
use RateLimit\RedisRateLimiter;
use Redis;

$rateLimiter = new RedisRateLimiter(new Redis());

$apiKey = 'abc123';

try {
    $rateLimiter->limit($apiKey, Rate::perMinute(100));
    
    //on success
} catch (LimitExceeded $exception) {
   //on limit exceeded
}
```

**Silent rate limiting**

```php
use RateLimit\Rate;
use RateLimit\RedisRateLimiter;
use Redis;

$rateLimiter = new RedisRateLimiter(new Redis());

$apiKey = 'abc123';
$status = $rateLimiter->limitSilently($apiKey, Rate::perMinute(100));

echo $status->getRemainingAttempts(); //99
```

> Or use MemcachedRateLimiter.
>
> Pass Memcached instance with enabled binary protocol

```php
$memcached = new Memcached();
$memcached->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
$memcached->addServer('127.0.0.1', 11211);

$rateLimiter = new MemcachedRateLimiter($memcached);
```

## Credits

- [Nikola Poša][link-author]
- [All Contributors][link-contributors]

## License

Released under MIT License - see the [License File](LICENSE) for details.


[link-author]: https://github.com/nikolaposa
[link-contributors]: ../../contributors
