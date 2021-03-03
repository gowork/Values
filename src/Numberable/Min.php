<?php declare(strict_types=1);

namespace GW\Value\Numberable;

use GW\Value\Arrayable;
use GW\Value\Arrayable\Map;
use GW\Value\Numberable;
use GW\Value\NumberValue;
use LogicException;
use function count;
use function min;

final class Min implements Numberable
{
    /** @var Arrayable<int|float> */
    private Arrayable $numbers;

    /** @param Arrayable<NumberValue> $numbers */
    public function __construct(Arrayable $numbers)
    {
        $this->numbers = new Map($numbers, new ToScalarNumber());
    }

    /** @return int|float */
    public function toNumber()
    {
        $numbers = $this->numbers->toArray();
        if (count($numbers) === 0) {
            throw new LogicException('Cannot calculate min number from empty set');
        }

        return min(...$numbers);
    }
}
