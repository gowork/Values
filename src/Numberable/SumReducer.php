<?php declare(strict_types=1);

namespace GW\Value\Numberable;

use GW\Value\Numberable;

final class SumReducer
{
    /**
     * @param int|float $sum
     * @return int|float
     */
    public function __invoke($sum, Numberable $next)
    {
        return $sum + $next->toNumber();
    }
}
