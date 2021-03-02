<?php declare(strict_types=1);

namespace GW\Value\Numberable;

use GW\Value\Numberable;

final class Formula implements Numberable
{
    private Numberable $numberable;
    /** @var callable(int|float):int|float */
    private $fn;

    /**
     * @param callable(int|float):int|float $fn
     */
    public function __construct(Numberable $numberable, callable $fn)
    {
        $this->numberable = $numberable;
        $this->fn = $fn;
    }

    /** @return int|float */
    public function toNumber()
    {
        return ($this->fn)($this->numberable->toNumber());
    }
}
