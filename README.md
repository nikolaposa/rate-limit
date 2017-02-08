# Rate Limit

[![Build Status](https://travis-ci.org/nikolaposa/rate-limit.svg?branch=master)](https://travis-ci.org/nikolaposa/rate-limit)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nikolaposa/rate-limit/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/nikolaposa/rate-limit/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/nikolaposa/rate-limit/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/nikolaposa/rate-limit/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/nikolaposa/rate-limit/v/stable)](https://packagist.org/packages/nikolaposa/rate-limit)
[![PDS Skeleton](https://img.shields.io/badge/pds-skeleton-blue.svg)](https://github.com/php-pds/skeleton)

Component that facilitates rate-limiting functionality. Although designed as a standalone, it also provides a middleware designed for API and/or other application endpoints that be used with any framework that supports the middleware concept.

## Installation

The preferred method of installation is via [Composer](http://getcomposer.org/). Run the following
command to install the latest version of a package and add it to your project's `composer.json`:

```bash
composer require nikolaposa/rate-limit
```

## Usage

Standalone:

```php
$rateLimiter = \RateLimit\RateLimiterFactory::createInMemoryRateLimiter(1000, 3600);

$rateLimit = $rateLimiter->hit('key');

echo $rateLimit->getIdentity(); //'key'
echo $rateLimit->getLimit(); //1000
echo $rateLimit->getRemainingAttempts(); //999
echo $rateLimit->getResetAt(); //1486503558
var_dump($rateLimit->isExceeded()); //false
```

**Note**: in-memory rate limiter should only be used for testing purposes. This package also provides Redis-backed rate limiter:

```php
$rateLimiter = \RateLimit\RateLimiterFactory::createRedisBackedRateLimiter([
    'host' => '10.0.0.7',
    'port' => 6379,
], 1000, 3600);
```

Zend Expressive example:

```php
$app = \Zend\Expressive\AppFactory::create();

$app->pipe(\RateLimit\Middleware\RateLimitMiddleware::createDefault(
   \RateLimit\RateLimiterFactory::createRedisBackedRateLimiter([
       'host' => '10.0.0.7',
       'port' => 6379,
   ], 1000, 3600)
));
```

Slim example:

```php
$app = new \Slim\App();

$app->add(\RateLimit\Middleware\RateLimitMiddleware::createDefault(
    \RateLimit\RateLimiterFactory::createRedisBackedRateLimiter([
       'host' => '10.0.0.7',
       'port' => 6379,
   ], 1000, 3600)
));
```

Whitelisting requests:

```php
use RateLimit\RequestsPerWindowRateLimiterFactory;
use Psr\Http\Message\RequestInterface;

$rateLimitMiddleware = \RateLimit\Middleware\RateLimitMiddleware::createDefault(
   \RateLimit\RateLimiterFactory::createRedisBackedRateLimiter([
        'host' => '10.0.0.7',
        'port' => 6379,
    ], 1000, 3600),
    [
        'whitelist' => function (RequestInterface $request) {
           if (false !== strpos($request->getUri()->getPath(), 'admin')) {
               return true;
           }
         
           return false;
        },
    ]
);
```

Custom limit exceeded handler:

```php
use RateLimit\RequestsPerWindowRateLimiterFactory;
use Zend\Diactoros\Response\JsonResponse;

$rateLimitMiddleware = \RateLimit\Middleware\RateLimitMiddleware::createDefault(
    \RateLimit\RateLimiterFactory::createRedisBackedRateLimiter([
        'host' => '10.0.0.7',
        'port' => 6379,
    ], 1000, 3600),
    [
        'limitExceededHandler' => function (RequestInterface $request) {
           return new JsonResponse([
               'message' => 'API rate limit exceeded',
           ], 429);
       },
    ]
);
```

## Author

**Nikola Poša**

* https://twitter.com/nikolaposa
* https://github.com/nikolaposa

## Copyright and license

Copyright 2017 Nikola Poša. Released under MIT License - see the `LICENSE` file for details.
