<?php declare(strict_types=1);

namespace GW\Value\Numberable;

use GW\Value\Numberable;

final class MultiplyReducer
{
    /**
     * @param int|float $product
     * @return int|float
     */
    public function __invoke($product, Numberable $next)
    {
        return $product * $next->toNumber();
    }
}
