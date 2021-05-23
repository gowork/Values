<?php declare(strict_types=1);

namespace GW\Value\Numberable;

use GW\Value\Arrayable\JustArray;
use GW\Value\Numberable;
use function array_values;

final class Add implements Numberable
{
    private Sum $sum;

    public function __construct(Numberable $term, Numberable ...$terms)
    {
        $this->sum = new Sum(new JustArray([$term, ...array_values($terms)]));
    }

    /** @return int|float */
    public function toNumber()
    {
        return $this->sum->toNumber();
    }
}
