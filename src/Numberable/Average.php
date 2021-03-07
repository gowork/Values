<?php declare(strict_types=1);

namespace GW\Value\Numberable;

use DivisionByZeroError;
use GW\Value\Arrayable;
use GW\Value\Arrayable\JustArray;
use GW\Value\Numberable;

final class Average implements Numberable
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
        $terms = $this->terms->toArray();
        $count = count($terms);
        if ($count === 0) {
            throw new DivisionByZeroError('Cannot calculate avg number from empty set');
        }

        return (new Sum(new JustArray($terms)))->toNumber() / $count;
    }
}
