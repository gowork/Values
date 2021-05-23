<?php declare(strict_types=1);

namespace GW\Value\Numberable;

use DivisionByZeroError;
use GW\Value\Numberable;

final class DivideReducer
{
    /**
     * @param int|float $fraction
     * @return int|float
     */
    public function __invoke($fraction, Numberable $divisor)
    {
        $divisorNum = $divisor->toNumber();
        if ($divisorNum === 0) {
            throw new DivisionByZeroError('Division by zero');
        }

        return $fraction / $divisorNum;
    }
}
