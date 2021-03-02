<?php declare(strict_types=1);

namespace GW\Value\Numberable;

use GW\Value\Numberable;
use function array_reduce;

final class Sum implements Numberable
{
    private Numberable $term;
    /** @var Numberable[] */
    private array $terms;

    public function __construct(Numberable $term, Numberable ...$terms)
    {
        $this->term = $term;
        $this->terms = $terms;
    }

    /** @return int|float */
    public function toNumber()
    {
        return array_reduce(
            $this->terms,
            /**
             * @param int|float $sum
             * @return int|float
             */
            static fn($sum, Numberable $next) => $sum + $next->toNumber(),
            $this->term->toNumber()
        );
    }
}
