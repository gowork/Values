<?php declare(strict_types=1);

namespace GW\Value\Stringable;

use GW\Value\StringValue;

final class ToNativeString
{
    public function __invoke(StringValue $stringValue): string
    {
        return $stringValue->toString();
    }
}
