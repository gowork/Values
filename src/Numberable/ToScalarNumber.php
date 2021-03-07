<?php declare(strict_types=1);

namespace GW\Value\Numberable;

use GW\Value\Numberable;

final class ToScalarNumber
{
    /** @return int|float */
    public function __invoke(Numberable $value)
    {
        return $value->toNumber();
    }
}
