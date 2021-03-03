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
     * @phpstan-return ArrayValue<TNewValue>
     */
    public function map(callable $transformer): ArrayValue;

    /**
     * @template TNewValue
     * @param callable(NumberValue):iterable<TNewValue> $transformer
     * @phpstan-return ArrayValue<TNewValue>
     */
    public function flatMap(callable $transformer): ArrayValue;

    /**
     * @template TNewKey
     * @param callable(NumberValue):TNewKey $reducer
     * @phpstan-return AssocValue<TNewKey, NumbersArray>
     */
    public function groupBy(callable $reducer): AssocValue;

    /**
     * @phpstan-return ArrayValue<array<int, NumberValue>>
     */
    public function chunk(int $size): ArrayValue;

    public function sort(callable $comparator): NumbersArray;

    public function shuffle(): NumbersArray;

    public function reverse(): NumbersArray;

    /**
     * @phpstan-param NumberValue $value
     */
    public function unshift($value): NumbersArray;

    /**
     * @phpstan-param NumberValue $value
     */
    public function shift(&$value = null): NumbersArray;

    /**
     * @phpstan-param NumberValue $value
     */
    public function push($value): NumbersArray;

    /**
     * @phpstan-param NumberValue $value
     */
    public function pop(&$value = null): NumbersArray;

    public function offsetExists($offset): bool;

    public function offsetGet($offset): NumberValue;

    public function offsetSet($offset, $value): void;

    public function offsetUnset($offset): void;

    /**
     * @phpstan-param Arrayable<NumberValue> $other
     */
    public function join(Arrayable $other): NumbersArray;

    public function slice(int $offset, int $length): NumbersArray;

    /**
     * @phpstan-param Arrayable<NumberValue>|null $replacement
     */
    public function splice(int $offset, int $length, ?Arrayable $replacement = null): NumbersArray;

    /**
     * @phpstan-param Arrayable<NumberValue> $other
     * @param (callable(NumberValue,NumberValue):int)|null $comparator
     */
    public function diff(Arrayable $other, ?callable $comparator = null): NumbersArray;

    /**
     * @phpstan-param Arrayable<NumberValue> $other
     * @param (callable(NumberValue,NumberValue):int)|null $comparator
     */
    public function intersect(Arrayable $other, ?callable $comparator = null): NumbersArray;

    /**
     * @template TNewValue
     * @param callable(TNewValue, NumberValue):TNewValue $transformer
     * @phpstan-param TNewValue $start
     * @phpstan-return TNewValue
     */
    public function reduce(callable $transformer, $start);

    public function implode(string $glue): StringValue;

    public function notEmpty(): NumbersArray;

    /**
     * @phpstan-return AssocValue<int, NumberValue>
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
     * @phpstan-param NumberValue $element
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
