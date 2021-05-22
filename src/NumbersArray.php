<?php declare(strict_types=1);

namespace GW\Value;

/**
 * @extends ArrayValue<NumberValue>
 */
interface NumbersArray extends ArrayValue
{
    public function sum(): NumberValue;

    public function average(): NumberValue;

    public function min(): NumberValue;

    public function max(): NumberValue;

    // ArrayValue

    /**
     * @param callable(NumberValue):void $callback
     */
    public function each(callable $callback): NumbersArray;

    /**
     * @param (callable(NumberValue $valueA, NumberValue $valueB):int)|null $comparator
     */
    public function unique(?callable $comparator = null): NumbersArray;

    /** @return NumberValue[] */
    public function toArray(): array;

    /**
     * @param callable(NumberValue):bool $filter
     */
    public function filter(callable $filter): NumbersArray;

    public function filterEmpty(): NumbersArray;

    /**
     * @template TNewValue
     * @param callable(NumberValue):TNewValue $transformer
     * @return ArrayValue<TNewValue>
     */
    public function map(callable $transformer): ArrayValue;

    /**
     * @template TNewValue
     * @param callable(NumberValue):iterable<TNewValue> $transformer
     * @return ArrayValue<TNewValue>
     */
    public function flatMap(callable $transformer): ArrayValue;

    /**
     * @template TNewKey
     * @param callable(NumberValue):TNewKey $reducer
     * @return AssocValue<TNewKey, ArrayValue<NumberValue>>
     */
    public function groupBy(callable $reducer): AssocValue;

    public function sort(callable $comparator): NumbersArray;

    public function shuffle(): NumbersArray;

    public function reverse(): NumbersArray;

    /**
     * @param NumberValue $value
     */
    public function unshift($value): NumbersArray;

    /**
     * @param NumberValue|null $value
     */
    public function shift(&$value = null): NumbersArray;

    /**
     * @param NumberValue $value
     */
    public function push($value): NumbersArray;

    /**
     * @param NumberValue|null $value
     */
    public function pop(&$value = null): NumbersArray;

    public function offsetExists($offset): bool;

    public function offsetGet($offset): NumberValue;

    public function offsetSet($offset, $value): void;

    public function offsetUnset($offset): void;

    /**
     * @param ArrayValue<NumberValue> $other
     */
    public function join(ArrayValue $other): NumbersArray;

    public function slice(int $offset, int $length): NumbersArray;

    /**
     * @param ArrayValue<NumberValue>|null $replacement
     */
    public function splice(int $offset, int $length, ?ArrayValue $replacement = null): NumbersArray;

    /**
     * @param ArrayValue<NumberValue> $other
     * @param (callable(NumberValue,NumberValue):int)|null $comparator
     */
    public function diff(ArrayValue $other, ?callable $comparator = null): NumbersArray;

    /**
     * @param ArrayValue<NumberValue> $other
     * @param (callable(NumberValue,NumberValue):int)|null $comparator
     */
    public function intersect(ArrayValue $other, ?callable $comparator = null): NumbersArray;

    /**
     * @template TNewValue
     * @param callable(TNewValue, NumberValue):TNewValue $transformer
     * @param TNewValue $start
     * @return TNewValue
     */
    public function reduce(callable $transformer, $start);

    /**
     * @param callable(NumberValue $reduced, NumberValue $item):NumberValue $transformer
     */
    public function reduceNumber(callable $transformer, NumberValue $start): NumberValue;

    public function implode(string $glue): StringValue;

    public function notEmpty(): NumbersArray;

    /**
     * @return AssocValue<int, NumberValue>
     */
    public function toAssocValue(): AssocValue;

    public function toStringsArray(): StringsArray;

    public function first(): ?NumberValue;

    public function last(): ?NumberValue;

    /**
     * @param callable(NumberValue):bool $filter
     */
    public function find(callable $filter): ?NumberValue;

    /**
     * @param callable(NumberValue):bool $filter
     */
    public function findLast(callable $filter): ?NumberValue;

    /**
     * @param NumberValue $element
     */
    public function hasElement($element): bool;

    /**
     * @param callable(NumberValue):bool $filter
     */
    public function any(callable $filter): bool;

    /**
     * @param callable(NumberValue):bool $filter
     */
    public function every(callable $filter): bool;
}
