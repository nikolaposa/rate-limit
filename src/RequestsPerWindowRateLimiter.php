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

use Psr\Http\Message\ServerRequestInterface;
use RateLimit\KeyGenerator\IpAddressKeyGenerator;
use RateLimit\Storage\StorageInterface;
use RateLimit\KeyGenerator\KeyGeneratorInterface;
use RateLimit\Exception\RequestsLimitExceededException;
use RateLimit\Exception\StorageRecordNotExistException;

/**
 * @author Nikola Posa <posa.nikola@gmail.com>
 */
final class RequestsPerWindowRateLimiter implements RateLimiterInterface
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var array
     */
    private $options;

    /**
     * @var KeyGeneratorInterface
     */
    private $keyGenerator;

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
    ];

    private function __construct(StorageInterface $storage, array $options, KeyGeneratorInterface $keyGenerator)
    {
        $this->storage = $storage;
        $this->options = $options;
        $this->keyGenerator = $keyGenerator;
    }

    public static function create(StorageInterface $storage, array $options = [], KeyGeneratorInterface $keyGenerator = null)
    {
        return new self(
            $storage,
            array_merge(self::$defaultOptions, $options),
            $keyGenerator ?? new IpAddressKeyGenerator()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ServerRequestInterface $serverRequest)
    {
        $key = $this->keyGenerator->getKey($serverRequest);

        $this->init($key);

        if ($this->isLimitExceeded()) {
            throw RequestsLimitExceededException::forRequestsLimitAndWindow($this->options['limit'], $this->options['window']);
        }

        if ($this->shouldReset()) {
            $this->reset();
        } else {
            $this->update();
        }

        $this->storage->set($key, $this->rateLimit);
    }

    private function init(string $key)
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

    private function update()
    {
        $this->rateLimit['remaining']--;
    }

    private function shouldReset() : bool
    {
        return time() >= $this->rateLimit['reset'];
    }

    private function reset()
    {
        $this->rateLimit = [
            'remaining' => $this->options['limit'],
            'reset' => time() + $this->options['window'],
        ];
    }
}
