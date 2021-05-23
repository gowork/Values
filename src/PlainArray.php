<?php declare(strict_types=1);

namespace GW\Value;

use ArrayIterator;
use BadMethodCallException;
use GW\Value\Arrayable\Associate;
use GW\Value\Arrayable\Cache;
use GW\Value\Arrayable\Chunk;
use GW\Value\Arrayable\DiffByComparator;
use GW\Value\Arrayable\DiffByString;
use GW\Value\Arrayable\Filter;
use GW\Value\Arrayable\FlatMap;
use GW\Value\Arrayable\IntersectByComparator;
use GW\Value\Arrayable\IntersectByString;
use GW\Value\Arrayable\Join;
use GW\Value\Arrayable\JustArray;
use GW\Value\Arrayable\Map;
use GW\Value\Arrayable\Reverse;
use GW\Value\Arrayable\Shuffle;
use GW\Value\Arrayable\Slice;
use GW\Value\Arrayable\Sort;
use GW\Value\Arrayable\Splice;
use GW\Value\Arrayable\UniqueByComparator;
use GW\Value\Arrayable\UniqueByString;
use GW\Value\Numberable\ToNumberValue;
use function array_map;
use function array_reverse;
use function count;
use function in_array;
use function is_array;

/**
 * @template TValue
 * @implements ArrayValue<TValue>
 */
final class PlainArray implements ArrayValue
{
    /** @phpstan-var Arrayable<TValue> */
    private Arrayable $items;

    /**
     * @phpstan-param array<mixed, TValue>|Arrayable<TValue> $items
     */
    public function __construct($items)
    {
        $this->items = is_array($items) ? new JustArray($items) : new Cache($items);
    }

    /**
     * @template TNewValue
     * @param callable(TValue $value):TNewValue $transformer
     * @phpstan-return PlainArray<TNewValue>
     */
    public function map(callable $transformer): PlainArray
    {
        return new self(new Map($this->items, $transformer));
    }

    /**
     * @template TNewValue
     * @param callable(TValue $value):iterable<TNewValue> $transformer
     * @phpstan-return PlainArray<TNewValue>
     */
    public function flatMap(callable $transformer): PlainArray
    {
        return new self(new FlatMap($this->items, $transformer));
    }

    /**
     * @template TNewKey
     * @phpstan-param callable(TValue $value):TNewKey $reducer
     * @phpstan-return AssocValue<TNewKey, ArrayValue<TValue>>
     */
    public function groupBy(callable $reducer): AssocValue
    {
        /** @phpstan-var array<TNewKey, array<TValue>> $groups */
        $groups = [];

        foreach ($this->items->toArray() as $item) {
            /** @phpstan-var TNewKey $key */
            $key = $reducer($item);
            $groups[$key][] = $item;
        }

        /** @phpstan-var array<TNewKey, ArrayValue<TValue>> $groupsWrapped */
        $groupsWrapped = array_map([Wrap::class, 'array'], $groups);

        return Wrap::assocArray($groupsWrapped);
    }

    /**
     * @phpstan-return PlainArray<array<int, TValue>>
     */
    public function chunk(int $size): PlainArray
    {
        return new self(new Chunk($this->items, $size));
    }

    /**
     * @phpstan-return PlainArray<TValue>
     */
    public function filterEmpty(): PlainArray
    {
        return $this->filter(Filters::notEmpty());
    }

    /**
     * @param callable(TValue $value):bool $filter
     * @phpstan-return PlainArray<TValue>
     */
    public function filter(callable $filter): PlainArray
    {
        return new self(new Filter($this->items, $filter));
    }

    /**
     * @param callable(TValue $leftValue, TValue $rightValue):int $comparator
     * @phpstan-return PlainArray<TValue>
     */
    public function sort(callable $comparator): PlainArray
    {
        return new self(new Sort($this->items, $comparator));
    }

    /**
     * @param callable(TValue $value):void $callback
     * @phpstan-return PlainArray<TValue>
     */
    public function each(callable $callback): PlainArray
    {
        foreach ($this->items->toArray() as $item) {
            $callback($item);
        }

        return $this;
    }

    /**
     * @phpstan-return PlainArray<TValue>
     */
    public function reverse(): PlainArray
    {
        return new self(new Reverse($this->items));
    }

    /**
     * @phpstan-param ArrayValue<TValue> $other
     * @phpstan-return PlainArray<TValue>
     */
    public function join(ArrayValue $other): PlainArray
    {
        return new self(new Join($this->items, $other));
    }

    /**
     * @phpstan-return PlainArray<TValue>
     */
    public function slice(int $offset, int $length): PlainArray
    {
        return new self(new Slice($this->items, $offset, $length));
    }

    /**
     * @phpstan-param ArrayValue<TValue> $replacement
     * @phpstan-return PlainArray<TValue>
     */
    public function splice(int $offset, int $length, ?ArrayValue $replacement = null): PlainArray
    {
        return new self(new Splice($this->items, $offset, $length, $replacement ?? new JustArray([])));
    }

    /**
     * @param callable(TValue $valueA, TValue $valueB):int | null $comparator
     * @phpstan-return PlainArray<TValue>
     */
    public function unique(?callable $comparator = null): PlainArray
    {
        if ($comparator === null) {
            return new self(new UniqueByString($this->items));
        }

        return new self(new UniqueByComparator($this->items, $comparator));
    }

    /**
     * @phpstan-param PlainArray<TValue> $other
     * @param (callable(TValue $valueA, TValue $valueB):int)|null $comparator
     * @phpstan-return PlainArray<TValue>
     */
    public function diff(ArrayValue $other, ?callable $comparator = null): PlainArray
    {
        if ($comparator === null) {
            return new self(new DiffByString($this->items, $other));
        }

        return new self(new DiffByComparator($this->items, $other, $comparator));
    }

    /**
     * @phpstan-param PlainArray<TValue> $other
     * @param (callable(TValue $valueA, TValue $valueB):int)|null $comparator
     * @phpstan-return PlainArray<TValue>
     */
    public function intersect(ArrayValue $other, ?callable $comparator = null): PlainArray
    {
        if ($comparator === null) {
            return new self(new IntersectByString($this->items, $other));
        }

        return new self(new IntersectByComparator($this->items, $other, $comparator));
    }

    /**
     * @phpstan-return PlainArray<TValue>
     */
    public function shuffle(): PlainArray
    {
        return new self(new Shuffle($this->items));
    }

    // adders and removers

    /**
     * @phpstan-param TValue $value
     * @phpstan-return PlainArray<TValue>
     */
    public function unshift($value): PlainArray
    {
        $items = $this->toArray();
        array_unshift($items, $value);

        return new self(new JustArray($items));
    }

    /**
     * @phpstan-param TValue $value
     * @phpstan-return PlainArray<TValue>
     */
    public function shift(&$value = null): PlainArray
    {
        $items = $this->toArray();
        $value = array_shift($items);

        return new self(new JustArray($items));;
    }

    /**
     * @phpstan-param TValue $value
     * @phpstan-return PlainArray<TValue>
     */
    public function push($value): PlainArray
    {
        $items = $this->toArray();
        $items[] = $value;

        return new self(new JustArray($items));
    }

    /**
     * @phpstan-param TValue $value
     * @phpstan-return PlainArray<TValue>
     */
    public function pop(&$value = null): PlainArray
    {
        $items = $this->toArray();
        $value = array_pop($items);

        return new self(new JustArray($items));
    }

    // finalizers

    /**
     * @template TNewValue
     * @param callable(TNewValue $reduced, TValue $value):TNewValue $transformer
     * @phpstan-param TNewValue $start
     * @phpstan-return TNewValue
     */
    public function reduce(callable $transformer, $start)
    {
        return array_reduce($this->items->toArray(), $transformer, $start);
    }

    /**
     * @phpstan-return ?TValue
     */
    public function first()
    {
        return $this->items->toArray()[0] ?? null;
    }

    /**
     * @phpstan-return ?TValue
     */
    public function last()
    {
        $count = $this->count();

        return $count > 0 ? $this->items->toArray()[$count - 1] : null;
    }

    /**
     * @param callable(TValue $value):bool $filter
     * @phpstan-return ?TValue
     */
    public function find(callable $filter)
    {
        foreach ($this->items->toArray() as $item) {
            if ($filter($item)) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @param callable(TValue $value):bool $filter
     * @phpstan-return ?TValue
     */
    public function findLast(callable $filter)
    {
        foreach (array_reverse($this->items->toArray()) as $item) {
            if ($filter($item)) {
                return $item;
            }
        }

        return null;
    }

    public function hasElement($element): bool
    {
        return in_array($element, $this->items->toArray(), true);
    }

    /**
     * @param callable(TValue $value):bool $filter
     */
    public function any(callable $filter): bool
    {
        foreach ($this->items->toArray() as $item) {
            if ($filter($item)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param callable(TValue $value):bool $filter
     */
    public function every(callable $filter): bool
    {
        foreach ($this->items->toArray() as $item) {
            if (!$filter($item)) {
                return false;
            }
        }

        return true;
    }

    public function count(): int
    {
        return count($this->items->toArray());
    }

    /**
     * @phpstan-return TValue[]
     */
    public function toArray(): array
    {
        return $this->items->toArray();
    }

    /**
     * @phpstan-return ArrayIterator<int, TValue>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items->toArray());
    }

    /**
     * @param int $offset
     */
    public function offsetExists($offset): bool
    {
        return isset($this->items->toArray()[$offset]);
    }

    /**
     * @param int $offset
     * @return ?TValue
     */
    public function offsetGet($offset)
    {
        return $this->items->toArray()[$offset];
    }

    /**
     * @param int $offset
     * @phpstan-param TValue $value
     * @throws BadMethodCallException For immutable types.
     */
    public function offsetSet($offset, $value): void
    {
        throw new BadMethodCallException('ArrayValue is immutable');
    }

    /**
     * @param int $offset
     * @throws BadMethodCallException For immutable types.
     */
    public function offsetUnset($offset): void
    {
        throw new BadMethodCallException('ArrayValue is immutable');
    }

    public function implode(string $glue): StringValue
    {
        return Wrap::string(implode($glue, $this->toArray()));
    }

    /**
     * @phpstan-return PlainArray<TValue>
     */
    public function notEmpty(): PlainArray
    {
        return $this->filter(Filters::notEmpty());
    }

    public function isEmpty(): bool
    {
        return $this->items->toArray() === [];
    }

    /**
     * @phpstan-return AssocValue<int, TValue>
     */
    public function toAssocValue(): AssocValue
    {
        return new AssocArray(new Associate($this->items));
    }

    public function toStringsArray(): StringsArray
    {
        return Wrap::stringsArray($this->items->toArray());
    }

    public function toNumbersArray(): NumbersArray
    {
        return new PlainNumbersArray($this->map(new ToNumberValue()));
    }
}
