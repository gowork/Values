<?php declare(strict_types=1);

namespace GW\Value\Numberable;

use GW\Value\NumberValue;

final class ToScalarNumber
{
    /** @return int|float */
    public function __invoke(NumberValue $value)
    {
        return $value->toNumber();
    }
}
