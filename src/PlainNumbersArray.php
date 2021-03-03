<?php declare(strict_types=1);

namespace GW\Value;

use GW\Value\Arrayable\Cache;
use GW\Value\Numberable\Average;
use GW\Value\Numberable\Max;
use GW\Value\Numberable\Min;
use GW\Value\Numberable\Sum;

/**
 * @extends GenericArray<NumberValue>
 */
final class PlainNumbersArray extends GenericArray
{
    /** @phpstan-var Arrayable<NumberValue> */
    private Arrayable $numbers;

    /** @param Arrayable<NumberValue> $numbers */
    public function __construct(Arrayable $numbers)
    {
        $this->numbers = new Cache($numbers);
    }

    /**
     * @param Arrayable<NumberValue> $items
     */
    public static function new(Arrayable $items): self
    {
        return new self($items);
    }

    /** @return Arrayable<NumberValue> */
    protected function items(): Arrayable
    {
        return $this->numbers;
    }

    public function sum(): NumberValue
    {
        return new PlainNumber(new Sum($this->numbers));
    }

    public function average(): NumberValue
    {
        return new PlainNumber(new Average($this->numbers));
    }

    public function min(): NumberValue
    {
        return new PlainNumber(new Min($this->numbers));
    }

    public function max(): NumberValue
    {
        return new PlainNumber(new Max($this->numbers));
    }
}
