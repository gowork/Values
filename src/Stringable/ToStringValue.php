<?php declare(strict_types=1);

namespace GW\Value\Stringable;

use GW\Value\StringValue;
use GW\Value\Wrap;
use InvalidArgumentException;
use function is_object;
use function is_scalar;
use function method_exists;

final class ToStringValue
{
    /** @param mixed $string */
    public function __invoke($string): StringValue
    {
        if ($string instanceof StringValue) {
            return $string;
        }

        if (is_scalar($string) || (is_object($string) && method_exists($string, '__toString'))) {
            return Wrap::string((string)$string);
        }

        throw new InvalidArgumentException('StringsValue can contain only StringValue');
    }
}
