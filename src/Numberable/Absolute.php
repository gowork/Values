<?php declare(strict_types=1);

namespace GW\Value\Numberable;

use GW\Value\Numberable;
use function abs;

final class Absolute implements Numberable
{
    private Numberable $numberable;

    public function __construct(Numberable $numberable)
    {
        $this->numberable = $numberable;
    }

    /** @return int|float */
    public function toNumber()
    {
        return abs($this->numberable->toNumber());
    }
}
