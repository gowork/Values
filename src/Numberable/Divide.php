<?php declare(strict_types=1);

namespace GW\Value\Numberable;

use GW\Value\Numberable;
use function array_reduce;

final class Divide implements Numberable
{
    private Numberable $dividend;
    /** @var Numberable[] */
    private array $divisors;

    public function __construct(Numberable $dividend, Numberable ...$divisors)
    {
        $this->dividend = $dividend;
        $this->divisors = $divisors;
    }

    public function toNumber()
    {
        return array_reduce($this->divisors, new DivideReducer(), $this->dividend->toNumber());
    }
}
