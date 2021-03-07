<?php declare(strict_types=1);

namespace GW\Value\Numberable;

use GW\Value\Numberable;
use GW\Value\NumberValue;
use GW\Value\PlainNumber;
use LogicException;
use function is_float;
use function is_int;

final class ToNumberValue
{
    /** @param mixed $number */
    public function __invoke($number): NumberValue
    {
        if ($number instanceof NumberValue) {
            return $number;
        }

        if ($number instanceof Numberable) {
            return new PlainNumber($number);
        }

        if (is_int($number)) {
            return new PlainNumber(new JustInteger($number));
        }

        if (is_float($number)) {
            return new PlainNumber(new JustFloat($number));
        }

        //TODO number from numeric string?

        throw new LogicException('Cannot cast value to NumberValue');
    }
}
