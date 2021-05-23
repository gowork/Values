<?php declare(strict_types=1);

namespace GW\Value\Numberable;

use GW\Value\Numberable;

final class JustInteger implements Numberable
{
    private int $integer;

    public function __construct(int $integer)
    {
        $this->integer = $integer;
    }

    public function toNumber(): int
    {
        return $this->integer;
    }
}
