<?php declare(strict_types=1);

namespace GW\Value;

use BadMethodCallException;
use IteratorAggregate;
use ArrayAccess;

/**
 * @template TValue
 * @extends Collection<TValue>
 * @extends IteratorAggregate<int, TValue>
 * @extends ArrayAccess<int, TValue>
 */
interface ArrayValue extends Value, Collection, Stack, IteratorAggregate, ArrayAccess
{
    /**
     * @param callable(TValue $value):void $callback
     * @phpstan-return ArrayValue<TValue>
     */
    public function each(callable $callback): ArrayValue;

    /**
     * @param (callable(TValue $valueA, TValue $valueB):int)|null $comparator
     * @phpstan-return ArrayValue<TValue>
     */
    public function unique(?callable $comparator = null): ArrayValue;

    /**
     * @phpstan-return array<int, TValue>
     */
    public function toArray(): array;

    /**
     * @param callable(TValue $value):bool $filter
     * @phpstan-return ArrayValue<TValue>
     */
    public function filter(callable $filter): ArrayValue;

    /**
     * @phpstan-return ArrayValue<TValue>
     */
    public function filterEmpty(): ArrayValue;

    /**
     * @template TNewValue
     * @param callable(TValue $value):TNewValue $transformer
     * @phpstan-return ArrayValue<TNewValue>
     */
    public function map(callable $transformer): ArrayValue;

    /**
     * @template TNewValue
     * @param callable(TValue $value):iterable<TNewValue> $transformer
     * @phpstan-return ArrayValue<TNewValue>
     */
    public function flatMap(callable $transformer): ArrayValue;

    /**
     * @template TNewKey
     * @param callable(TValue $value):TNewKey $reducer
     * @phpstan-return AssocValue<TNewKey, ArrayValue<TValue>>
     */
    public function groupBy(callable $reducer): AssocValue;

    /**
     * @phpstan-return ArrayValue<array<int, TValue>>
     */
    public function chunk(int $size): ArrayValue;

    /**
     * @param callable(TValue $valueA, TValue $valueB): int $comparator
     * @phpstan-return ArrayValue<TValue>
     */
    public function sort(callable $comparator): ArrayValue;

    /**
     * @phpstan-return ArrayValue<TValue>
     */
    public function shuffle(): ArrayValue;

    /**
     * @phpstan-return ArrayValue<TValue>
     */
    public function reverse(): ArrayValue;

    // Stack

    /**
     * @phpstan-param TValue $value
     * @phpstan-return ArrayValue<TValue>
     */
    public function unshift($value): ArrayValue;

    /**
     * @phpstan-param TValue $value
     * @phpstan-return ArrayValue<TValue>
     */
    public function shift(&$value = null): ArrayValue;

    /**
     * @phpstan-param TValue $value
     * @phpstan-return ArrayValue<TValue>
     */
    public function push($value): ArrayValue;

    /**
     * @phpstan-param TValue $value
     * @phpstan-return ArrayValue<TValue>
     */
    public function pop(&$value = null): ArrayValue;

    // ArrayAccess

    /**
     * @param int $offset
     */
    public function offsetExists($offset): bool;

    /**
     * @param int $offset
     * @phpstan-return TValue
     */
    public function offsetGet($offset);

    /**
     * @param int $offset
     * @phpstan-param TValue $value
     * @throws BadMethodCallException For immutable types.
     */
    public function offsetSet($offset, $value): void;

    /**
     * @param int $offset
     * @return void
     * @throws BadMethodCallException For immutable types.
     */
    public function offsetUnset($offset): void;

    // ArrayValue own

    /**
     * @phpstan-param ArrayValue<TValue> $other
     * @phpstan-return ArrayValue<TValue>
     */
    public function join(ArrayValue $other): ArrayValue;

    /**
     * @phpstan-return ArrayValue<TValue>
     */
    public function slice(int $offset, int $length): ArrayValue;

    /**
     * @phpstan-return ArrayValue<TValue>
     */
    public function skip(int $length): ArrayValue;

    /**
     * @phpstan-return ArrayValue<TValue>
     */
    public function take(int $length): ArrayValue;

    /**
     * @phpstan-param ArrayValue<TValue> $replacement
     * @phpstan-return ArrayValue<TValue>
     */
    public function splice(int $offset, int $length, ?ArrayValue $replacement = null): ArrayValue;

    /**
     * @phpstan-param ArrayValue<TValue> $other
     * @param (callable(TValue,TValue):int)|null $comparator
     * @phpstan-return ArrayValue<TValue>
     */
    public function diff(ArrayValue $other, ?callable $comparator = null): ArrayValue;

    /**
     * @phpstan-param ArrayValue<TValue> $other
     * @param (callable(TValue,TValue):int)|null $comparator
     * @phpstan-return ArrayValue<TValue>
     */
    public function intersect(ArrayValue $other, ?callable $comparator = null): ArrayValue;

    /**
     * @template TNewValue
     * @param callable(TNewValue, TValue):TNewValue $transformer
     * @phpstan-param TNewValue $start
     * @phpstan-return TNewValue
     */
    public function reduce(callable $transformer, $start);

    public function implode(string $glue): StringValue;

    /**
     * @phpstan-return ArrayValue<TValue>
     */
    public function notEmpty(): ArrayValue;

    /**
     * @phpstan-return AssocValue<int, TValue>
     */
    public function toAssocValue(): AssocValue;

    public function toStringsArray(): StringsArray;
}
