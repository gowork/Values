<?php declare(strict_types=1);

namespace GW\Value\Numberable;

use GW\Value\Numberable;
use function floor;

final class Floor implements Numberable
{
    private Numberable $numberable;

    public function __construct(Numberable $numberable)
    {
        $this->numberable = $numberable;
    }

    /** @return int|float */
    public function toNumber()
    {
        return floor($this->numberable->toNumber());
    }
}
