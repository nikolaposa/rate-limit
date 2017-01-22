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
use RateLimit\Options\RequestsPerWindowOptions;
use RateLimit\Exception\StorageRecordNotExistException;

/**
 * @author Nikola Posa <posa.nikola@gmail.com>
 */
final class RequestsPerWindowRateLimiter extends AbstractRateLimiter
{
    const LIMIT_EXCEEDED_HTTP_STATUS_CODE = 429; //HTTP 429 "Too Many Requests" (RFC 6585)

    const HEADER_LIMIT = 'X-RateLimit-Limit';
    const HEADER_REMAINING = 'X-RateLimit-Remaining';
    const HEADER_RESET = 'X-RateLimit-Reset';

    /**
     * @var RequestsPerWindowOptions
     */
    private $options;

    /**
     * @var array
     */
    private $rateLimit;

    public function __construct(StorageInterface $storage, IdentityGeneratorInterface $identityGenerator, RequestsPerWindowOptions $options)
    {
        parent::__construct($storage, $identityGenerator);
        
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $out = null)
    {
        $identity = $this->identityGenerator->getIdentity($request);

        $this->initRateLimit($identity);

        if ($this->shouldResetRateLimit()) {
            $this->resetRateLimit();
        } elseif ($this->isLimitExceeded()) {
            return $this->onLimitExceeded($request, $response);
        } else {
            $this->updateRateLimit();
        }

        $this->storage->set($identity, $this->rateLimit);

        return $this->onBelowLimit($request, $response, $out);
    }

    private function initRateLimit(string $identity)
    {
        try {
            $rateLimit = $this->storage->get($identity);
        } catch (StorageRecordNotExistException $ex) {
            $rateLimit = [
                'remaining' => $this->options->getLimit(),
                'reset' => time() + $this->options->getWindow(),
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
            'remaining' => $this->options->getLimit(),
            'reset' => time() + $this->options->getWindow(),
        ];
    }

    private function onLimitExceeded(RequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $response = $this
            ->setRateLimitHeaders($response)
            ->withStatus(self::LIMIT_EXCEEDED_HTTP_STATUS_CODE)
        ;

        $limitExceededHandler = $this->options->getLimitExceededHandler();
        $response = $limitExceededHandler($request, $response);

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
            ->withHeader(self::HEADER_LIMIT, (string) $this->options->getLimit())
            ->withHeader(self::HEADER_REMAINING, (string) $this->rateLimit['remaining'])
            ->withHeader(self::HEADER_RESET, (string) $this->rateLimit['reset'])
        ;
    }
}
