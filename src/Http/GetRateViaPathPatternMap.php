<?php

declare(strict_types=1);

namespace RateLimit\Http;

use Assert\Assertion;
use Psr\Http\Message\ServerRequestInterface;
use RateLimit\Rate;

final class GetRateViaPathPatternMap implements GetRate
{
    /** @var array */
    private $pathPatternRateMap;

    public function __construct(array $pathPatternRateMap)
    {
        Assertion::allString(array_keys($pathPatternRateMap), 'Map keys must be string patterns');
        Assertion::allIsInstanceOf($pathPatternRateMap, Rate::class, 'Map values must be ' . Rate::class . ' instances');

        $this->pathPatternRateMap = $pathPatternRateMap;
    }

    public function forRequest(ServerRequestInterface $request): ?Rate
    {
        $path = $request->getUri()->getPath();

        foreach ($this->pathPatternRateMap as $pattern => $rate) {
            if (preg_match($pattern, $path)) {
                return $rate;
            }
        }

        return null;
    }
}
