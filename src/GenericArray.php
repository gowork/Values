<?php declare(strict_types=1);

namespace GW\Value;

use ArrayIterator;
use BadMethodCallException;
use GW\Value\Arrayable\Associate;
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
use function array_map;
use function array_reverse;
use function count;
use function in_array;

/**
 * @template TValue
 * @implements ArrayValue<TValue>
 */
abstract class GenericArray implements ArrayValue
{
    /** @return Arrayable<TValue> */
    abstract protected function items(): Arrayable;

    /**
     * @template TNewValue
     * @param Arrayable<TNewValue> $items
     * @return static<TNewValue>
     */
    abstract public static function new(Arrayable $items): self;

    /**
     * @template TNewValue
     * @param callable(TValue $value):TNewValue $transformer
     * @phpstan-return static<TNewValue>
     */
    public function map(callable $transformer): self
    {
        return static::new(new Map($this->items(), $transformer));
    }

    /**
     * @template TNewValue
     * @param callable(TValue $value):iterable<TNewValue> $transformer
     * @phpstan-return static<TNewValue>
     */
    public function flatMap(callable $transformer): self
    {
        return static::new(new FlatMap($this->items(), $transformer));
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

        foreach ($this->items()->toArray() as $item) {
            /** @phpstan-var TNewKey $key */
            $key = $reducer($item);
            $groups[$key][] = $item;
        }

        /** @phpstan-var array<TNewKey, ArrayValue<TValue>> $groupsWrapped */
        $groupsWrapped = array_map([Wrap::class, 'array'], $groups);

        return Wrap::assocArray($groupsWrapped);
    }

    /**
     * @phpstan-return static<array<int, TValue>>
     */
    public function chunk(int $size): self
    {
        return static::new(new Chunk($this->items(), $size));
    }

    /**
     * @phpstan-return static<TValue>
     */
    public function filterEmpty(): self
    {
        return $this->filter(Filters::notEmpty());
    }

    /**
     * @param callable(TValue $value):bool $filter
     * @phpstan-return static<TValue>
     */
    public function filter(callable $filter): self
    {
        return static::new(new Filter($this->items(), $filter));
    }

    /**
     * @param callable(TValue $leftValue, TValue $rightValue):int $comparator
     * @phpstan-return static<TValue>
     */
    public function sort(callable $comparator): self
    {
        return static::new(new Sort($this->items(), $comparator));
    }

    /**
     * @param callable(TValue $value):void $callback
     * @phpstan-return static<TValue>
     */
    public function each(callable $callback): self
    {
        foreach ($this->items()->toArray() as $item) {
            $callback($item);
        }

        return $this;
    }

    /**
     * @phpstan-return static<TValue>
     */
    public function reverse(): self
    {
        return static::new(new Reverse($this->items()));
    }

    /**
     * @phpstan-param ArrayValue<TValue> $other
     * @phpstan-return static<TValue>
     */
    public function join(ArrayValue $other): self
    {
        return static::new(new Join($this->items(), $other));
    }

    /**
     * @phpstan-return static<TValue>
     */
    public function slice(int $offset, int $length): self
    {
        return static::new(new Slice($this->items(), $offset, $length));
    }

    /**
     * @phpstan-param ArrayValue<TValue> $replacement
     * @phpstan-return static<TValue>
     */
    public function splice(int $offset, int $length, ?ArrayValue $replacement = null): self
    {
        return static::new(new Splice($this->items(), $offset, $length, $replacement ?? new JustArray([])));
    }

    /**
     * @param callable(TValue $valueA, TValue $valueB):int | null $comparator
     * @phpstan-return static<TValue>
     */
    public function unique(?callable $comparator = null): self
    {
        if ($comparator === null) {
            return static::new(new UniqueByString($this->items()));
        }

        return static::new(new UniqueByComparator($this->items(), $comparator));
    }

    /**
     * @phpstan-param static<TValue> $other
     * @param (callable(TValue $valueA, TValue $valueB):int)|null $comparator
     * @phpstan-return static<TValue>
     */
    public function diff(ArrayValue $other, ?callable $comparator = null): self
    {
        if ($comparator === null) {
            return static::new(new DiffByString($this->items(), $other));
        }

        return static::new(new DiffByComparator($this->items(), $other, $comparator));
    }

    /**
     * @phpstan-param static<TValue> $other
     * @param (callable(TValue $valueA, TValue $valueB):int)|null $comparator
     * @phpstan-return static<TValue>
     */
    public function intersect(ArrayValue $other, ?callable $comparator = null): self
    {
        if ($comparator === null) {
            return static::new(new IntersectByString($this->items(), $other));
        }

        return static::new(new IntersectByComparator($this->items(), $other, $comparator));
    }

    /**
     * @phpstan-return static<TValue>
     */
    public function shuffle(): self
    {
        return static::new(new Shuffle($this->items()));
    }

    // adders and removers

    /**
     * @phpstan-param TValue $value
     * @phpstan-return static<TValue>
     */
    public function unshift($value): self
    {
        $items = $this->toArray();
        array_unshift($items, $value);

        return static::new(new JustArray($items));
    }

    /**
     * @phpstan-param TValue $value
     * @phpstan-return static<TValue>
     */
    public function shift(&$value = null): self
    {
        $items = $this->toArray();
        $value = array_shift($items);

        return static::new(new JustArray($items));;
    }

    /**
     * @phpstan-param TValue $value
     * @phpstan-return static<TValue>
     */
    public function push($value): self
    {
        $items = $this->toArray();
        $items[] = $value;

        return static::new(new JustArray($items));
    }

    /**
     * @phpstan-param TValue $value
     * @phpstan-return static<TValue>
     */
    public function pop(&$value = null): self
    {
        $items = $this->toArray();
        $value = array_pop($items);

        return static::new(new JustArray($items));
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
        return array_reduce($this->items()->toArray(), $transformer, $start);
    }

    /**
     * @phpstan-return ?TValue
     */
    public function first()
    {
        return $this->items()->toArray()[0] ?? null;
    }

    /**
     * @phpstan-return ?TValue
     */
    public function last()
    {
        $count = $this->count();

        return $count > 0 ? $this->items()->toArray()[$count - 1] : null;
    }

    /**
     * @param callable(TValue $value):bool $filter
     * @phpstan-return ?TValue
     */
    public function find(callable $filter)
    {
        foreach ($this->items()->toArray() as $item) {
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
        foreach (array_reverse($this->items()->toArray()) as $item) {
            if ($filter($item)) {
                return $item;
            }
        }

        return null;
    }

    public function hasElement($element): bool
    {
        return in_array($element, $this->items()->toArray(), true);
    }

    /**
     * @param callable(TValue $value):bool $filter
     */
    public function any(callable $filter): bool
    {
        foreach ($this->items()->toArray() as $item) {
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
        foreach ($this->items()->toArray() as $item) {
            if (!$filter($item)) {
                return false;
            }
        }

        return true;
    }

    public function count(): int
    {
        return count($this->items()->toArray());
    }

    /**
     * @phpstan-return TValue[]
     */
    public function toArray(): array
    {
        return $this->items()->toArray();
    }

    /**
     * @phpstan-return ArrayIterator<int, TValue>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items()->toArray());
    }

    /**
     * @param int $offset
     */
    public function offsetExists($offset): bool
    {
        return isset($this->items()->toArray()[$offset]);
    }

    /**
     * @param int $offset
     * @return ?TValue
     */
    public function offsetGet($offset)
    {
        return $this->items()->toArray()[$offset];
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
     * @phpstan-return static<TValue>
     */
    public function notEmpty(): self
    {
        return $this->filter(Filters::notEmpty());
    }

    public function isEmpty(): bool
    {
        return $this->items()->toArray() === [];
    }

    /**
     * @phpstan-return AssocValue<int, TValue>
     */
    public function toAssocValue(): AssocValue
    {
        return new AssocArray(new Associate($this->items()));
    }

    public function toStringsArray(): StringsArray
    {
        return Wrap::stringsArray($this->items()->toArray());
    }
}
