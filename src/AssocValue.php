<?php

namespace GW\Value;

use IteratorAggregate;
use ArrayAccess;
interface AssocValue extends Value, Collection, IteratorAggregate, ArrayAccess
{
    // Collection

    /**
     * @param callable $callback function(mixed $value): void
     * @return AssocValue
     */
    public function each(callable $callback): AssocValue;

    /**
     * @param callable|null $comparator function(mixed $valueA, mixed $valueB): int{-1, 0, 1}
     * @return AssocValue
     */
    public function unique(?callable $comparator = null): AssocValue;

    /**
     * @param callable $filter function(mixed $value): bool
     * @return AssocValue
     */
    public function filter(callable $filter): AssocValue;

    /**
     * @return AssocValue
     */
    public function filterEmpty(): AssocValue;

    /**
     * @param callable $transformer function(mixed $value[, string $key]): mixed
     * @return AssocValue
     */
    public function map(callable $transformer): AssocValue;

    /**
     * @param callable $comparator function(mixed $valueA, mixed $valueB): int{-1, 0, 1}
     * @return AssocValue
     */
    public function sort(callable $comparator): AssocValue;

    /**
     * @return AssocValue
     */
    public function shuffle(): AssocValue;

    /**
     * @return AssocValue
     */
    public function reverse(): AssocValue;

    // ArrayAccess

    /**
     * @param string $offset
     */
    public function offsetExists($offset): bool;

    /**
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset);

    /**
     * @param string $offset
     * @param mixed $value
     * @return void
     * @throws \BadMethodCallException For immutable types.
     */
    public function offsetSet($offset, $value): void;

    /**
     * @param string $offset
     * @return void
     * @throws \BadMethodCallException For immutable types.
     */
    public function offsetUnset($offset): void;

    // AssocValue own

    public function toAssocArray(): array;

    public function keys(): StringsArray;

    public function values(): ArrayValue;

    /**
     * @param callable $transformer function(string $key[, mixed $value]): string
     * @return AssocValue
     */
    public function mapKeys(callable $transformer): AssocValue;

    /**
     * @param callable $comparator function(string $keyA, string $keyB): int{-1, 1}
     * @return AssocValue
     */
    public function sortKeys(callable $comparator): AssocValue;

    /**
     * @param mixed $value
     * @return AssocValue
     */
    public function with(string $key, $value): AssocValue;

    /**
     * @return AssocValue
     */
    public function without(string ...$keys): AssocValue;

    /**
     * @return AssocValue
     */
    public function only(string ...$keys): AssocValue;

    /**
     * @param mixed $value
     * @return AssocValue
     */
    public function withoutElement($value): AssocValue;

    /**
     * @param AssocValue $other
     * @return AssocValue
     */
    public function merge(AssocValue $other): AssocValue;

    /**
     * @param callable $transformer function(mixed $reduced, mixed $value, string $key): mixed
     * @param mixed $start
     * @return mixed
     */
    public function reduce(callable $transformer, $start);

    /**
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null);

    public function has(string $key): bool;
}
