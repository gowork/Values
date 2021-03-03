<?php declare(strict_types=1);

namespace GW\Value\Numberable;

use GW\Value\Arrayable;
use GW\Value\Numberable;
use function array_reduce;

final class Sum implements Numberable
{
    /** @var Arrayable<Numberable> */
    private Arrayable $terms;

    /** @param Arrayable<Numberable> $terms */
    public function __construct(Arrayable $terms)
    {
        $this->terms = $terms;
    }

    /** @return int|float */
    public function toNumber()
    {
        return array_reduce(
            $this->terms->toArray(),
            /**
             * @param int|float $sum
             * @return int|float
             */
            static fn($sum, Numberable $next) => $sum + $next->toNumber(),
            0
        );
    }
}
