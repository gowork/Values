<?php declare(strict_types=1);

namespace GW\Value;

use RuntimeException;
use ArrayIterator;
use BadMethodCallException;
use function array_combine;
use function array_keys;
use function array_map;
use function in_array;
use function count;

/**
 * @template TKey
 * @template TValue
 * @implements AssocValue<TKey, TValue>
 */
final class AssocArray implements AssocValue
{
    /** @phpstan-var array<TKey, TValue> */
    private array $items;

    /**
     * @phpstan-param array<TKey, TValue> $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * @template TNewValue
     * @phpstan-param callable(TValue $value, TKey $key=):TNewValue $transformer
     * @phpstan-return AssocArray<TKey, TNewValue>
     */
    public function map(callable $transformer): AssocArray
    {
        $result = [];
        foreach ($this->items as $key => $value) {
            $result[$key] = $transformer($value, $key);
        }

        /** @phpstan-var array<TKey, TNewValue> $result */
        return new self($result);
    }

    /**
     * @phpstan-return ArrayValue<TKey>
     */
    public function keys(): ArrayValue
    {
        return Wrap::array(array_keys($this->items));
    }

    /**
     * @template TNewKey
     * @param callable(TKey $key, TValue $value=): TNewKey $transformer
     * @phpstan-return AssocArray<TNewKey, TValue>
     */
    public function mapKeys(callable $transformer): AssocArray
    {
        /** @var array<TNewKey, TValue>|false $combined */
        $combined = array_combine(array_map($transformer, array_keys($this->items), $this->items), $this->items);

        if ($combined === false) {
            throw new RuntimeException('Cannot map keys - combined array is broken.');
        }

        return new self($combined);
    }

    /**
     * @phpstan-return AssocArray<TKey, TValue>
     */
    public function filterEmpty(): AssocArray
    {
        return $this->filter(Filters::notEmpty());
    }

    /**
     * @param callable(TValue $value):bool $filter
     * @phpstan-return AssocArray<TKey, TValue>
     */
    public function filter(callable $filter): AssocArray
    {
        return new self(array_filter($this->items, $filter));
    }

    /**
     * @param callable(TValue $valueA, TValue $valueB):int $comparator
     * @phpstan-return AssocArray<TKey, TValue>
     */
    public function sort(callable $comparator): AssocArray
    {
        $items = $this->items;
        uasort($items, $comparator);

        return new self($items);
    }

    /**
     * @phpstan-return AssocArray<TKey, TValue>
     */
    public function reverse(): AssocArray
    {
        return new self(array_reverse($this->items, true));
    }

    /**
     * @phpstan-return AssocArray<TKey, TValue>
     */
    public function shuffle(): AssocArray
    {
        $items = $this->items;
        shuffle($items);

        return new self($items);
    }

    /**
     * @param callable(TKey $keyA, TKey $keyB): int $comparator
     * @phpstan-return AssocArray<TKey, TValue>
     */
    public function sortKeys(callable $comparator): AssocArray
    {
        $items = $this->items;
        uksort($items, $comparator);

        return new self($items);
    }

    /**
     * @phpstan-param callable(TValue $value, TKey $key=):void $callback
     * @phpstan-return AssocArray<TKey, TValue>
     */
    public function each(callable $callback): AssocArray
    {
        foreach ($this->items as $key => $item) {
            $callback($item, $key);
        }

        return $this;
    }

    /**
     * @phpstan-param (callable(TValue $valueA, TValue $valueB):int)|null $comparator
     * @phpstan-return AssocArray<TKey, TValue>
     */
    public function unique(?callable $comparator = null): AssocArray
    {
        if ($comparator === null) {
            return new self(array_unique($this->items));
        }

        $result = [];
        foreach ($this->items as $keyA => $valueA) {
            foreach ($result as $valueB) {
                if ($comparator($valueA, $valueB) === 0) {
                    // item already in result
                    continue 2;
                }
            }

            $result[$keyA] = $valueA;
        }

        /** @phpstan-var array<TKey, TValue> $result */
        return new self($result);
    }

    /**
     * @phpstan-param TKey $key
     * @phpstan-param TValue $value
     * @phpstan-return AssocArray<TKey, TValue>
     */
    public function with($key, $value): AssocArray
    {
        /** @phpstan-var array<TKey, TValue> $items */
        $items = [$key => $value];

        return $this->merge(new self($items));
    }

    /**
     * @phpstan-param AssocValue<TKey, TValue> $other
     * @phpstan-return AssocArray<TKey, TValue>
     */
    public function merge(AssocValue $other): AssocArray
    {
        return new self(array_merge($this->items, $other->toAssocArray()));
    }

    /**
     * @phpstan-param array<int, TKey> $keys
     * @phpstan-return AssocArray<TKey, TValue>
     */
    public function without(...$keys): AssocArray
    {
        return new self(array_diff_key($this->items, array_flip($keys)));
    }

    /**
     * @phpstan-param array<int, TKey> $keys
     * @phpstan-return AssocArray<TKey, TValue>
     */
    public function only(...$keys): AssocArray
    {
        return new self(array_intersect_key($this->items, array_flip($keys)));
    }

    /**
     * @phpstan-param TValue $value
     * @phpstan-return AssocArray<TKey, TValue>
     */
    public function withoutElement($value): AssocArray
    {
        return $this->filter(Filters::notEqual($value));
    }

    /**
     * @template TNewValue
     * @param callable(TNewValue $reduced, TValue $value, string $key):TNewValue $transformer
     * @phpstan-param TNewValue $start
     * @phpstan-return TNewValue
     */
    public function reduce(callable $transformer, $start)
    {
        $reduced = $start;

        foreach ($this->items as $key => $value) {
            $reduced = $transformer($reduced, $value, $key);
        }

        return $reduced;
    }

    /**
     * @phpstan-param TKey $key
     * @phpstan-param ?TValue $default
     * @phpstan-return ?TValue
     */
    public function get($key, $default = null)
    {
        return $this->items[$key] ?? $default;
    }

    /**
     * @phpstan-param TKey $key
     */
    public function has($key): bool
    {
        return isset($this->items[$key]);
    }

    /**
     * @return ?TValue
     */
    public function first()
    {
        return $this->values()->first();
    }

    /**
     * @return ArrayValue<TValue>
     */
    public function values(): ArrayValue
    {
        return Wrap::array($this->items);
    }

    /**
     * @phpstan-return ?TValue
     */
    public function last()
    {
        return $this->values()->last();
    }

    /**
     * @param callable(TValue $value): bool $filter
     * @phpstan-return ?TValue
     */
    public function find(callable $filter)
    {
        return $this->values()->find($filter);
    }

    /**
     * @param callable(TValue $value): bool $filter
     * @phpstan-return ?TValue
     */
    public function findLast(callable $filter)
    {
        return $this->values()->findLast($filter);
    }

    /**
     * @phpstan-param TValue $element
     */
    public function hasElement($element): bool
    {
        return in_array($element, $this->items, true);
    }

    /**
     * @param callable(TValue $value):bool $filter
     */
    public function any(callable $filter): bool
    {
        return $this->values()->any($filter);
    }

    /**
     * @param callable(TValue $value):bool $filter
     */
    public function every(callable $filter): bool
    {
        return $this->values()->every($filter);
    }

    /**
     * @return array<int, TValue>
     */
    public function toArray(): array
    {
        return $this->values()->toArray();
    }

    /**
     * @return array<TKey, TValue>
     */
    public function toAssocArray(): array
    {
        return $this->items;
    }

    /**
     * @return ArrayIterator<TKey, TValue>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    /**
     * @param TKey $offset
     */
    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * @param TKey $offset
     * @return ?TValue
     */
    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        throw new BadMethodCallException('AssocArray is immutable');
    }

    public function offsetUnset($offset): void
    {
        throw new BadMethodCallException('AssocArray is immutable');
    }
}
