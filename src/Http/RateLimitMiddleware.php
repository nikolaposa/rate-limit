<?php

declare(strict_types=1);

namespace RateLimit\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use RateLimit\SilentRateLimiter;
use RateLimit\Status;

final class RateLimitMiddleware implements MiddlewareInterface
{
    public const HEADER_LIMIT = 'X-RateLimit-Limit';
    public const HEADER_REMAINING = 'X-RateLimit-Remaining';
    public const HEADER_RESET = 'X-RateLimit-Reset';

    /** @var SilentRateLimiter */
    private $rateLimiter;

    /** @var GetRate */
    private $getRate;

    /** @var ResolveIdentifier */
    private $resolveIdentifier;

    /** @var RequestHandlerInterface */
    private $limitExceededHandler;

    public function __construct(
        SilentRateLimiter $rateLimiter,
        GetRate $getRate,
        ResolveIdentifier $resolveIdentifier,
        RequestHandlerInterface $limitExceededHandler
    ) {
        $this->rateLimiter = $rateLimiter;
        $this->getRate = $getRate;
        $this->resolveIdentifier = $resolveIdentifier;
        $this->limitExceededHandler = $limitExceededHandler;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $rate = $this->getRate->forRequest($request);

        if (null === $rate) {
            return $handler->handle($request);
        }

        $identifier = $this->resolveIdentifier->fromRequest($request);

        $status = $this->rateLimiter->limitSilently($identifier, $rate);

        if ($status->limitExceeded()) {
            return $this->setRateLimitHeaders($this->limitExceededHandler->handle($request), $status)
                ->withStatus(429);
        }

        return $this->setRateLimitHeaders($handler->handle($request), $status);
    }

    private function setRateLimitHeaders(ResponseInterface $response, Status $rateLimitStatus): ResponseInterface
    {
        return $response
            ->withHeader(self::HEADER_LIMIT, (string) $rateLimitStatus->getLimit())
            ->withHeader(self::HEADER_REMAINING, (string) $rateLimitStatus->getRemainingAttempts())
            ->withHeader(self::HEADER_RESET, (string) $rateLimitStatus->getResetAt()->getTimestamp());
    }
}
