# Rate Limit

[![Build Status](https://travis-ci.org/nikolaposa/rate-limit.svg?branch=master)](https://travis-ci.org/nikolaposa/rate-limit)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nikolaposa/rate-limit/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/nikolaposa/rate-limit/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/nikolaposa/rate-limit/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/nikolaposa/rate-limit/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/nikolaposa/rate-limit/v/stable)](https://packagist.org/packages/nikolaposa/rate-limit)

Rate limiting middleware designed for API and/or other application endpoints. Although it's framework-agnostic, it can be used with any framework that supports the middleware concept.

## Installation

The preferred method of installation is via [Composer](http://getcomposer.org/). Run the following
command to install the latest version of a package and add it to your project's `composer.json`:

```bash
composer require nikolaposa/rate-limit
```

## Rate Limit strategies

This package supports creating different rate limiting strategies based on the `RateLimit\RateLimiterInterface` interface. Yet some default strategies are already provided and ready to use:

**RequestsPerWindowRateLimiter**

This implementation does rate limiting based on specified number of requests per time window (number of seconds).

Zend Expressive usage example:

```php
$app = \Zend\Expressive\AppFactory::create();

$app->pipe(RateLimit\RequestsPerWindowRateLimiterFactory::createInMemoryRateLimiter([
    'limit' => 1000,
    'window' => 3600,
]));
```

Slim usage example:

```php
$app = new \Slim\App();

$app->add(\RateLimit\RequestsPerWindowRateLimiterFactory::createInMemoryRateLimiter([
  'limit' => 1000,
  'window' => 3600,
]));
```

## Author

**Nikola Poša**

* https://twitter.com/nikolaposa
* https://github.com/nikolaposa

## Copyright and license

Copyright 2017 Nikola Poša. Released under MIT License - see the `LICENSE` file for details.
