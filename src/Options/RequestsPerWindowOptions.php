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

namespace RateLimit\Options;

/**
 * @author Nikola Posa <posa.nikola@gmail.com>
 */
class RequestsPerWindowOptions implements OptionsInterface
{
    /**
     * @var int
     */
    protected $limit;

    /**
     * @var int
     */
    protected $window;

    /**
     * @var callable
     */
    protected $limitExceededHandler;

    public function __construct(int $limit, int $window, callable $limitExceededHandler)
    {
        $this->limit = $limit;
        $this->window = $window;
        $this->limitExceededHandler = $limitExceededHandler;
    }

    public function getLimit() : int
    {
        return $this->limit;
    }

    public function getWindow() : int
    {
        return $this->window;
    }

    public function getLimitExceededHandler() : callable
    {
        return $this->limitExceededHandler;
    }
}
