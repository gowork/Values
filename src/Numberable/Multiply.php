<?php declare(strict_types=1);

namespace GW\Value\Numberable;

use GW\Value\Numberable;
use function array_reduce;

final class Multiply implements Numberable
{
    private Numberable $factor;
    /** @var Numberable[] */
    private array $factors;

    public function __construct(Numberable $factor, Numberable ...$factors)
    {
        $this->factor = $factor;
        $this->factors = $factors;
    }

    public function toNumber()
    {
        return array_reduce(
            $this->factors,
            /**
             * @param int|float $product
             * @return int|float
             */
            static fn($product, Numberable $next) => $product * $next->toNumber(),
            $this->factor->toNumber()
        );
    }
}
