<?php declare(strict_types=1);

namespace GW\Value\Numberable;

use GW\Value\Numberable;

final class Modulo implements Numberable
{
    private Numberable $dividend;
    private Numberable $divisor;

    public function __construct(Numberable $dividend, Numberable $divisor)
    {
        $this->dividend = $dividend;
        $this->divisor = $divisor;
    }

    public function toNumber(): int
    {
        return $this->dividend->toNumber() % $this->divisor->toNumber();
    }
}
