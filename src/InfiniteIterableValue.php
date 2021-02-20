<?php declare(strict_types=1);

namespace GW\Value;

use function count;

/**
 * @template TKey
 * @template TValue
 * @implements IterableValue<TKey, TValue>
 */
final class InfiniteIterableValue implements IterableValue
{
    /** @phpstan-var IterableValueStack<TKey, TValue> */
    private IterableValueStack $stack;

    /** @phpstan-param iterable<TKey, TValue> $iterable */
    public function __construct(iterable $iterable)
    {
        $this->stack = new IterableValueStack(new IterableValueIterator($iterable));
    }

    /**
     * @template TNewKey
     * @template TNewValue
     * @param IterableValueStack<TNewKey, TNewValue> $stack
     * @return self<TNewKey, TNewValue>
     */
    private function withStack(IterableValueStack $stack): self
    {
        $clone = clone $this;
        $clone->stack = $stack;

        return $clone;
    }

    /**
     * @param callable(TValue $value):void $callback
     * @phpstan-return InfiniteIterableValue<TKey, TValue>
     */
    public function each(callable $callback): InfiniteIterableValue
    {
        foreach ($this->stack->iterate() as $value) {
            $callback($value);
        }

        return $this;
    }

    /**
     * @param callable(TValue $valueA, TValue $valueB):int | null $comparator
     * @phpstan-return InfiniteIterableValue<TKey, TValue>
     */
    public function unique(?callable $comparator = null): InfiniteIterableValue
    {
        if ($comparator === null) {
            $comparator = fn($a, $b) => $a <=> $b;
        }

        $knownValues = [];

        return $this->filter(
            static function ($valueA) use (&$knownValues, $comparator) {
                foreach ($knownValues as $valueB) {
                    if ($comparator($valueA, $valueB) === 0) {
                        return false;
                    }
                }

                $knownValues[] = $valueA;

                return true;
            }
        );
    }

    /**
     * @phpstan-return TValue[]
     */
    public function toArray(): array
    {
        $return = [];

        foreach ($this->stack->iterate() as $value) {
            $return[] = $value;
        }

        return $return;
    }

    /**
     * @phpstan-param callable(TValue $value):bool $filter
     * @phpstan-return InfiniteIterableValue<TKey, TValue>
     */
    public function filter(callable $filter): InfiniteIterableValue
    {
        $clone = clone $this;
        $clone->stack = $clone->stack->push(
            /**
             * @phpstan-param iterable<TKey, TValue> $iterable
             * @phpstan-return iterable<TKey, TValue>
             */
            static function (iterable $iterable) use ($filter): iterable {
                foreach ($iterable as $value) {
                    if ($filter($value)) {
                        yield $value;
                    }
                }
            }
        );

        return $clone;
    }

    /**
     * @phpstan-return InfiniteIterableValue<TKey, TValue>
     */
    public function filterEmpty(): InfiniteIterableValue
    {
        return $this->filter(Filters::notEmpty());
    }

    /**
     * @template TNewValue
     * @param callable(TValue $value): TNewValue $transformer
     * @phpstan-return InfiniteIterableValue<TKey, TNewValue>
     */
    public function map(callable $transformer): InfiniteIterableValue
    {
        return $this->withStack($this->stack->push(
            /**
             * @phpstan-param iterable<TKey, TValue> $iterable
             * @phpstan-return iterable<TKey, TNewValue>
             */
            static function (iterable $iterable) use ($transformer): iterable {
                foreach ($iterable as $value) {
                    yield $transformer($value);
                }
            }
        ));
    }

    /**
     * @template TNewValue
     * @param callable(TValue $value): iterable<TNewValue> $transformer
     * @phpstan-return InfiniteIterableValue<TKey, TNewValue>
     */
    public function flatMap(callable $transformer): InfiniteIterableValue
    {
        return $this->withStack($this->stack->push(
            /**
             * @phpstan-param iterable<TKey, TValue> $iterable
             * @phpstan-return iterable<TKey, TNewValue>
             */
            static function (iterable $iterable) use ($transformer): iterable {
                foreach ($iterable as $value) {
                    yield from $transformer($value);
                }
            }
        ));
    }

    /**
     * @phpstan-return ArrayValue<TValue>
     */
    public function toArrayValue(): ArrayValue
    {
        return Wrap::array($this->toArray());
    }

    /**
     * @phpstan-param TValue $value
     * @phpstan-return InfiniteIterableValue<TKey, TValue>
     */
    public function unshift($value): InfiniteIterableValue
    {
        return $this->withStack($this->stack->push(
            /**
             * @phpstan-param iterable<TKey, TValue> $iterable
             * @phpstan-return iterable<TKey, TValue>
             */
            static function (iterable $iterable) use ($value): iterable {
                yield $value;
                yield from $iterable;
            }
        ));
    }

    /**
     * @phpstan-param TValue $value
     * @phpstan-return InfiniteIterableValue<TKey, TValue>
     */
    public function push($value): InfiniteIterableValue
    {
        return $this->withStack($this->stack->push(
            /**
             * @phpstan-param iterable<TKey, TValue> $iterable
             * @phpstan-return iterable<TKey, TValue>
             */
            static function (iterable $iterable) use ($value): iterable {
                yield from $iterable;
                yield $value;
            }
        ));
    }

    /**
     * @phpstan-param iterable<TKey, TValue> $other
     * @phpstan-return InfiniteIterableValue<TKey, TValue>
     */
    public function join(iterable $other): InfiniteIterableValue
    {
        return $this->withStack($this->stack->push(
            /**
             * @phpstan-param iterable<TKey, TValue> $iterable
             * @phpstan-return iterable<TKey, TValue>
             */
            static function (iterable $iterable) use ($other): iterable {
                yield from $iterable;
                yield from $other;
            }
        ));
    }

    /**
     * @phpstan-return InfiniteIterableValue<TKey, TValue>
     */
    public function slice(int $offset, int $length): InfiniteIterableValue
    {
        return $this->withStack($this->stack->push(
            /**
             * @phpstan-param iterable<TKey, TValue> $iterable
             * @phpstan-return iterable<TKey, TValue>
             */
            static function (iterable $iterable) use ($offset, $length): iterable {
                foreach ($iterable as $value) {
                    if ($offset-- > 0) {
                        continue;
                    }

                    yield $value;

                    if (--$length <= 0) {
                        break;
                    }
                }
            }
        ));
    }

    /**
     * @phpstan-param ArrayValue<TValue> $other
     * @param callable(TValue $valueA, TValue $valueB):int | null $comparator
     * @phpstan-return InfiniteIterableValue<TKey, TValue>
     */
    public function diff(ArrayValue $other, ?callable $comparator = null): InfiniteIterableValue
    {
        return $this->withStack($this->stack->push(
            /**
             * @phpstan-param iterable<TKey, TValue> $iterable
             * @phpstan-return iterable<TKey, TValue>
             */
            static function (iterable $iterable) use ($other, $comparator): iterable {
                foreach ($iterable as $value) {
                    if ($comparator === null) {
                        $found = $other->hasElement($value);
                    } else {
                        $found = $other->any(fn($otherValue): bool => $comparator($otherValue, $value) === 0);
                    }

                    if ($found) {
                        continue;
                    }

                    yield $value;
                }
            }
        ));
    }

    /**
     * @phpstan-param ArrayValue<TValue> $other
     * @param callable(TValue $valueA, TValue $valueB):int | null $comparator
     * @phpstan-return InfiniteIterableValue<TKey, TValue>
     */
    public function intersect(ArrayValue $other, ?callable $comparator = null): InfiniteIterableValue
    {
        return $this->withStack($this->stack->push(
            /**
             * @phpstan-param iterable<TKey, TValue> $iterable
             * @phpstan-return iterable<TKey, TValue>
             */
            static function (iterable $iterable) use ($other, $comparator): iterable {
                foreach ($iterable as $value) {
                    if ($comparator === null) {
                        $found = $other->hasElement($value);
                    } else {
                        $found = $other->any(fn($otherValue): bool => $comparator($otherValue, $value) === 0);
                    }

                    if (!$found) {
                        continue;
                    }

                    yield $value;
                }
            }
        ));
    }

    /**
     * @template TNewValue
     * @param callable(TNewValue $reduced, TValue $value): TNewValue $transformer
     * @phpstan-param TNewValue $start
     * @phpstan-return TNewValue
     */
    public function reduce(callable $transformer, $start)
    {
        foreach ($this->stack->iterate() as $value) {
            $start = $transformer($start, $value);
        }

        return $start;
    }

    /**
     * @param callable(TValue $value):bool $filter
     * @return ?TValue
     */
    public function find(callable $filter)
    {
        foreach ($this->stack->iterate() as $item) {
            if ($filter($item)) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @param callable(TValue $value):bool $filter
     * @return ?TValue
     */
    public function findLast(callable $filter)
    {
        $last = null;

        foreach ($this->stack->iterate() as $item) {
            if ($filter($item)) {
                $last = $item;
            }
        }

        return $last;
    }

    /**
     * @param callable(TValue $value):bool $filter
     */
    public function any(callable $filter): bool
    {
        foreach ($this->stack->iterate() as $value) {
            if ($filter($value)) {
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
        foreach ($this->stack->iterate() as $value) {
            if (!$filter($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @phpstan-return InfiniteIterableValue<int, array<int, TValue>>
     */
    public function chunk(int $size): InfiniteIterableValue
    {
        return $this->withStack($this->stack->push(
            /**
             * @phpstan-param iterable<TKey, TValue> $iterable
             * @phpstan-return iterable<TKey, TValue[]>
             */
            static function (iterable $iterable) use ($size): iterable {
                $buffer = [];

                foreach ($iterable as $item) {
                    $buffer[] = $item;

                    if (count($buffer) === $size) {
                        yield $buffer;
                        $buffer = [];
                    }
                }

                if ($buffer !== []) {
                    yield $buffer;
                }
            }
        ));
    }

    /**
     * @phpstan-return InfiniteIterableValue<TKey, TValue>
     */
    public function flatten(): InfiniteIterableValue
    {
        $clone = clone $this;
        $clone->stack = $this->stack->push(
            /**
             * @phpstan-param iterable<TKey, TValue> $iterable
             * @phpstan-return iterable<TKey, TValue>
             */
            static function (iterable $iterable): iterable {
                foreach ($iterable as $item) {
                    yield from $item;
                }
            }
        );

        return $clone;
    }

    /**
     * @phpstan-return InfiniteIterableValue<TKey, TValue>
     */
    public function notEmpty(): InfiniteIterableValue
    {
        return $this->filter(Filters::notEmpty());
    }

    /**
     * @phpstan-return ?TValue
     */
    public function first()
    {
        foreach ($this->stack->iterate() as $value) {
            return $value;
        }

        return null;
    }

    /**
     * @phpstan-return ?TValue
     */
    public function last()
    {
        $value = null;
        foreach ($this->stack->iterate() as $value) {}

        return $value;
    }

    /**
     * @phpstan-return iterable<TKey, TValue>
     */
    public function getIterator(): iterable
    {
        yield from $this->stack->iterate();
    }
}
