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

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RateLimit\Storage\StorageInterface;
use RateLimit\Identity\IdentityGeneratorInterface;
use RateLimit\Identity\IpAddressIdentityGenerator;
use RateLimit\Exception\StorageRecordNotExistException;

/**
 * @author Nikola Posa <posa.nikola@gmail.com>
 */
final class RequestsPerWindowRateLimiter implements RateLimiterInterface
{
    const LIMIT_EXCEEDED_HTTP_STATUS_CODE = 429; //HTTP 429 "Too Many Requests" (RFC 6585)

    const HEADER_LIMIT = 'X-RateLimit-Limit';
    const HEADER_REMAINING = 'X-RateLimit-Remaining';
    const HEADER_RESET = 'X-RateLimit-Reset';

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var array
     */
    private $options;

    /**
     * @var IdentityGeneratorInterface
     */
    private $identityGenerator;

    /**
     * @var array
     */
    private $rateLimit;

    /**
     * @var array
     */
    private static $defaultOptions = [
        'limit' => 100,
        'window' => 900, //15 minutes
        'limitExceededHandler' => null,
    ];

    private function __construct(StorageInterface $storage, array $options, IdentityGeneratorInterface $identityGenerator)
    {
        $this->storage = $storage;
        $this->options = $options;
        $this->identityGenerator = $identityGenerator;
    }

    public static function create(StorageInterface $storage, array $options = [], IdentityGeneratorInterface $identityGenerator = null)
    {
        return new self(
            $storage,
            array_merge(self::$defaultOptions, $options),
            $identityGenerator ?? new IpAddressIdentityGenerator()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $out = null)
    {
        $key = $this->identityGenerator->getIdentity($request);

        $this->initRateLimit($key);

        if ($this->isLimitExceeded()) {
            return $this->onLimitExceeded($request, $response);
        }

        if ($this->shouldResetRateLimit()) {
            $this->resetRateLimit();
        } else {
            $this->updateRateLimit();
        }

        $this->storage->set($key, $this->rateLimit);

        return $this->onBelowLimit($request, $response, $out);
    }

    private function initRateLimit(string $key)
    {
        try {
            $rateLimit = $this->storage->get($key);
        } catch (StorageRecordNotExistException $ex) {
            $rateLimit = [
                'remaining' => $this->options['limit'],
                'reset' => time() + $this->options['window'],
            ];
        }

        $this->rateLimit = $rateLimit;
    }

    private function isLimitExceeded() : bool
    {
        return $this->rateLimit['remaining'] <= 0;
    }

    private function updateRateLimit()
    {
        $this->rateLimit['remaining']--;
    }

    private function shouldResetRateLimit() : bool
    {
        return time() >= $this->rateLimit['reset'];
    }

    private function resetRateLimit()
    {
        $this->rateLimit = [
            'remaining' => $this->options['limit'],
            'reset' => time() + $this->options['window'],
        ];
    }

    private function onLimitExceeded(RequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $response = $this
            ->setRateLimitHeaders($response)
            ->withStatus(self::LIMIT_EXCEEDED_HTTP_STATUS_CODE)
        ;

        $limitExceededHandler = $this->options['limitExceededHandler'];

        if (null !== $limitExceededHandler && is_callable($limitExceededHandler)) {
            $response = $limitExceededHandler($request, $response);
        }

        return $response;
    }

    private function onBelowLimit(RequestInterface $request, ResponseInterface $response, callable $out = null) : ResponseInterface
    {
        $response = $this->setRateLimitHeaders($response);

        return $out ? $out($request, $response) : $response;
    }

    private function setRateLimitHeaders(ResponseInterface $response) : ResponseInterface
    {
        return $response
            ->withHeader(self::HEADER_LIMIT, (string) $this->options['limit'])
            ->withHeader(self::HEADER_REMAINING, (string) $this->rateLimit['remaining'])
            ->withHeader(self::HEADER_RESET, (string) $this->rateLimit['reset'])
        ;
    }
}
