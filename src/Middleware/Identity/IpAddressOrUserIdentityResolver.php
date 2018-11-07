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

namespace RateLimit\Middleware\Identity;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Vassilis Poursalidis <poursal@gmail.com>
 */
final class IpAddressOrUserIdentityResolver extends AbstractIdentityResolver
{
    const IP_KEY_PREFIX   = 'rlimit-ip-';
    const USER_KEY_PREFIX = 'rlimit-id-';

    /**
     * @var array
     */
    protected $loadBalancers;

    /**
     * @var string
     */
    protected $authKeyName;

    public function __construct(array $loadBalancers = [], string $authKeyName = 'authUserId')
    {
        $this->loadBalancers = $loadBalancers;
        $this->authKeyName   = $authKeyName;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(RequestInterface $request) : string
    {
        if (!$request instanceof ServerRequestInterface) {
            return self::getDefaultIdentity($request);
        }

        $serverParams = $request->getServerParams();
        $authUserId   = $request->getAttribute($this->authKeyName);

        if ( !empty($authUserId) ) {
            return self::USER_KEY_PREFIX . $authUserId;
        }

        if ( !empty($serverParams['REMOTE_ADDR']) && in_array($serverParams['REMOTE_ADDR'], $this->loadBalancers) ) {
            if (array_key_exists('HTTP_CLIENT_IP', $serverParams)) {
                return self::IP_KEY_PREFIX . $serverParams['HTTP_CLIENT_IP'];
            }

            if (array_key_exists('HTTP_X_FORWARDED_FOR', $serverParams)) {
                return self::IP_KEY_PREFIX . $serverParams['HTTP_X_FORWARDED_FOR'];
            }
        }

        return $serverParams['REMOTE_ADDR'] ? (self::IP_KEY_PREFIX . $serverParams['REMOTE_ADDR']) : self::getDefaultIdentity($request);
    }
}
