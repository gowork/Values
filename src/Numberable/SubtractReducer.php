<?php declare(strict_types=1);

namespace GW\Value\Numberable;

use GW\Value\Numberable;

final class SubtractReducer
{
    /**
     * @param int|float $diff
     * @return int|float
     */
    public function __invoke($diff, Numberable $next)
    {
        return $diff - $next->toNumber();
    }
}
