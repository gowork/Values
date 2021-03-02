<?php declare(strict_types=1);

namespace GW\Value;

interface Numberable
{
    /**
     * @return int|float
     */
    public function toNumber();
}
