# Rate Limit

[![Build](https://github.com/nikolaposa/rate-limit/workflows/Build/badge.svg?branch=master)](https://github.com/nikolaposa/rate-limit/actions)
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

**Terminating rate limiter**

```php
use RateLimit\Exception\LimitExceeded;
use RateLimit\Rate;
use RateLimit\RedisRateLimiter;
use Redis;

$rateLimiter = new RedisRateLimiter(Rate::perMinute(100), new Redis());

$apiKey = 'abc123';

try {
    $rateLimiter->limit($apiKey);
    
    //on success
} catch (LimitExceeded $exception) {
   //on limit exceeded
}
```

**Silent rate limiter**

```php
use RateLimit\Rate;
use RateLimit\RedisRateLimiter;
use Redis;

$rateLimiter = new RedisRateLimiter(Rate::perMinute(100), new Redis());

$ipAddress = '192.168.1.2';
$status = $rateLimiter->limitSilently($ipAddress);

echo $status->getRemainingAttempts(); //99
```

**Practical usage with configuring multiple rate limiting strategies in a project**

```php
use RateLimit\Rate;
use RateLimit\RedisRateLimiter;

$container->set('rate_limiter.api', new RedisRateLimiter(Rate::perSecond(10), $container->get('redis')));
$container->set('rate_limiter.videos', new RedisRateLimiter(Rate::perDay(5), $container->get('redis')));
```

## Supported drivers

- [Redis](src/RedisRateLimiter.php)
- [Predis](src/PredisRateLimiter.php)
- [Memcached](src/MemcachedRateLimiter.php)
- [APCu](src/ApcuRateLimiter.php)
- [In-memory](src/InMemoryRateLimiter.php)

## Credits

- [Nikola Poša][link-author]
- [All Contributors][link-contributors]

## License

Released under MIT License - see the [License File](LICENSE) for details.


[link-author]: https://github.com/nikolaposa
[link-contributors]: ../../contributors
