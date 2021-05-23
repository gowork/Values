<?php declare(strict_types=1);

namespace GW\Value\Numberable;

use GW\Value\Numberable;
use function ceil;

final class Ceil implements Numberable
{
    private Numberable $numberable;

    public function __construct(Numberable $numberable)
    {
        $this->numberable = $numberable;
    }

    /** @return int|float */
    public function toNumber()
    {
        return ceil($this->numberable->toNumber());
    }
}
