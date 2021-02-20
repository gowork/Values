<?php declare(strict_types=1);

namespace GW\Value;

use ArrayAccess;
use BadMethodCallException;
use IteratorAggregate;

/**
 * @extends IteratorAggregate<int, StringValue>
 * @extends ArrayAccess<int, StringValue>
 */
interface StringsArray extends Value, IteratorAggregate, ArrayAccess, StringValue
{
    /**
     * @param callable(StringValue $value): void $callback
     */
    public function each(callable $callback): StringsArray;

    /**
     * @param callable(StringValue $valueA, StringValue $valueB):int | null $comparator
     */
    public function unique(?callable $comparator = null): StringsArray;

    /**
     * @return array<int, StringValue>
     */
    public function toArray(): array;

    /**
     * @return string[]
     */
    public function toNativeStrings(): array;

    /**
     * @param callable(StringValue $value): bool $filter
     */
    public function filter(callable $filter): StringsArray;

    public function filterEmpty(): StringsArray;

    /**
     * @param callable(StringValue $value):StringValue $transformer
     */
    public function map(callable $transformer): StringsArray;

    /**
     * @param callable(StringValue $value):iterable<StringValue> $transformer
     */
    public function flatMap(callable $transformer): StringsArray;

    /**
     * @template TNewKey
     * @param callable(StringValue $value):TNewKey $reducer
     * @phpstan-return AssocValue<TNewKey, StringsArray>
     */
    public function groupBy(callable $reducer): AssocValue;

    /**
     * @param callable(StringValue $valueA, StringValue $valueB):int $comparator
     */
    public function sort(callable $comparator): StringsArray;

    public function shuffle(): StringsArray;

    public function reverse(): StringsArray;

    /**
     * @param StringValue|string $value
     */
    public function unshift($value): StringsArray;

    /**
     * @param StringValue|null $value
     */
    public function shift(&$value = null): StringsArray;

    /**
     * @param StringValue|string $value
     */
    public function push($value): StringsArray;

    /**
     * @param StringValue|null $value
     */
    public function pop(&$value = null): StringsArray;

    /**
     * @param int $offset
     */
    public function offsetExists($offset): bool;

    /**
     * @param int $offset
     */
    public function offsetGet($offset): StringValue;

    /**
     * @param int $offset
     * @param StringValue|string $value
     * @return void
     * @throws BadMethodCallException For immutable types.
     */
    public function offsetSet($offset, $value): void;

    /**
     * @param int $offset
     * @return void
     * @throws BadMethodCallException For immutable types.
     */
    public function offsetUnset($offset): void;

    public function join(StringsArray $other): StringsArray;

    public function slice(int $offset, int $length): StringsArray;

    public function splice(int $offset, int $length, ?StringsArray $replacement = null): StringsArray;

    /**
     * @param (callable(StringValue, StringValue):int)|null $comparator
     */
    public function diff(StringsArray $other, ?callable $comparator = null): StringsArray;

    /**
     * @param (callable(StringValue $valueA, StringValue $valueB):int)|null $comparator
     */
    public function intersect(StringsArray $other, ?callable $comparator = null): StringsArray;

    /**
     * @template TNewValue
     * @phpstan-param callable(TNewValue, StringValue):TNewValue $transformer
     * @phpstan-param TNewValue $start
     * @phpstan-return TNewValue
     */
    public function reduce(callable $transformer, $start);

    public function implode(string $glue): StringValue;

    /**
     * @return StringsArray
     */
    public function notEmpty(): StringsArray;

    /**
     * @return StringValue|null
     */
    public function first(): ?StringValue;

    /**
     * @return StringValue|null
     */
    public function last(): ?StringValue;

    /**
     * @param callable(StringValue $value): bool $filter
     */
    public function find(callable $filter): ?StringValue;

    /**
     * @param callable(StringValue $value): bool $filter
     */
    public function findLast(callable $filter): ?StringValue;

    // StringValue

    /**
     * @param callable(string $value):(StringValue|string) $transformer
     */
    public function transform(callable $transformer): StringsArray;

    public function stripTags(): StringsArray;

    /**
     * @param string|StringValue $characterMask
     */
    public function trim($characterMask = self::TRIM_MASK): StringsArray;

    /**
     * @param string|StringValue $characterMask
     */
    public function trimRight($characterMask = self::TRIM_MASK): StringsArray;

    /**
     * @param string|StringValue $characterMask
     */
    public function trimLeft($characterMask = self::TRIM_MASK): StringsArray;

    public function lower(): StringsArray;

    public function upper(): StringsArray;

    public function lowerFirst(): StringsArray;

    public function upperFirst(): StringsArray;

    public function upperWords(): StringsArray;

    /**
     * @param string|StringValue $string
     */
    public function padRight(int $length, $string = ' '): StringsArray;

    /**
     * @param string|StringValue $string
     */
    public function padLeft(int $length, $string = ' '): StringsArray;

    /**
     * @param string|StringValue $string
     */
    public function padBoth(int $length, $string = ' '): StringsArray;

    /**
     * @param string|StringValue $search
     * @param string|StringValue $replace
     */
    public function replace($search, $replace): StringsArray;

    /**
     * @return StringsArray
     * @param array<int,string>|ArrayValue<string> $search
     * @param string|StringValue $replace
     */
    public function replaceAll($search, $replace): StringsArray;

    /**
     * @return StringsArray
     * @param array<string,string>|AssocValue<string,string> $pairs
     */
    public function replacePairs($pairs): StringsArray;

    /**
     * @param string|StringValue $pattern
     * @param string|StringValue $replacement
     */
    public function replacePattern($pattern, $replacement): StringsArray;

    /**
     * @param string|StringValue $pattern
     */
    public function replacePatternCallback($pattern, callable $callback): StringsArray;

    /**
     * @param string|StringValue $postfix
     */
    public function truncate(int $length, $postfix = '...'): StringsArray;

    public function substring(int $start, ?int $length = null): StringsArray;

    /**
     * @param string|StringValue $other
     */
    public function postfix($other): StringsArray;

    /**
     * @param string|StringValue $other
     */
    public function prefix($other): StringsArray;

    /**
     * @return ArrayValue<StringValue>
     */
    public function toArrayValue(): ArrayValue;

    /**
     * @return AssocValue<int, StringValue>
     */
    public function toAssocValue(): AssocValue;

    /**
     * @phpstan-return ArrayValue<array<int, StringValue>>
     */
    public function chunk(int $size): ArrayValue;

    public function toStringsArray(): StringsArray;
}
