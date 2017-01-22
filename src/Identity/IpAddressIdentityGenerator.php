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

namespace RateLimit\Identity;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Nikola Posa <posa.nikola@gmail.com>
 */
final class IpAddressIdentityGenerator implements IdentityGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function getIdentity(RequestInterface $request) : string
    {
        if (!$request instanceof ServerRequestInterface) {
            return 'ANONYMOUS';
        }

        $serverParams = $request->getServerParams();

        if (array_key_exists('HTTP_CLIENT_IP', $serverParams)) {
            return $serverParams['HTTP_CLIENT_IP'];
        }

        if (array_key_exists('HTTP_X_FORWARDED_FOR', $serverParams)) {
            return $serverParams['HTTP_X_FORWARDED_FOR'];
        }

        return $serverParams['REMOTE_ADDR'] ?? 'UNKNOWN';
    }
}
