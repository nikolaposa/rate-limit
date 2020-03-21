# Rate Limit

[![Build Status](https://travis-ci.org/nikolaposa/rate-limit.svg?branch=master)](https://travis-ci.org/nikolaposa/rate-limit)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nikolaposa/rate-limit/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/nikolaposa/rate-limit/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/nikolaposa/rate-limit/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/nikolaposa/rate-limit/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/nikolaposa/rate-limit/v/stable)](https://packagist.org/packages/nikolaposa/rate-limit)
[![PDS Skeleton](https://img.shields.io/badge/pds-skeleton-blue.svg)](https://github.com/php-pds/skeleton)


General purpose rate limiter featuring implementation that uses Redis as backend. Also provides PSR-15 middleware suitable for API or other application endpoints.

## Installation

The preferred method of installation is via [Composer](http://getcomposer.org/). Run the following
command to install the latest version of a package and add it to your project's `composer.json`:

```bash
composer require nikolaposa/rate-limit
```

## Usage

### General purpose

```php
use RateLimit\Rate;
use RateLimit\RedisRateLimiter;
use Redis;

$rateLimiter = new RedisRateLimiter(new Redis());

$apiKey = 'abc123';
$status = $rateLimiter->handle($apiKey, Rate::perMinute(100));

echo $status->getRemainingAttempts(); //99
```

### Middleware

**Laminas**

```php
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Server;
use Laminas\Stratigility\Middleware\NotFoundHandler;
use Laminas\Stratigility\MiddlewarePipe;
use RateLimit\Http\RateLimitMiddleware;
use RateLimit\Rate;
use RateLimit\RedisRateLimiter;
use Redis;

$rateLimitMiddleware = new RateLimitMiddleware(
    new RedisRateLimiter(new Redis()),
    new GetRateViaPathPatternMap([
        '|/api/posts|' => Rate::perMinute(3),
        '|/api/users|' => Rate::perSecond(1),
    ]),
    new ResolveIdentifierFromIpAddress(),
    new class implements RequestHandlerInterface {
        public function handle(ServerRequestInterface $request): ResponseInterface
        {
            return new JsonResponse(['error' => 'Too many requests']);
        }
    }
);

$app = new MiddlewarePipe();
$app->pipe($rateLimitMiddleware);

$server = Server::createServer([$app, 'handle'], $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
$server->listen(function ($req, $res) {
    return $res;
});
```

## Credits

- [Nikola Poša][link-author]
- [All Contributors][link-contributors]

## License

Released under MIT License - see the [License File](LICENSE) for details.


[link-author]: https://github.com/nikolaposa
[link-contributors]: ../../contributors
