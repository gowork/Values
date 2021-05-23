<?php declare(strict_types=1);

namespace GW\Value;

use BadMethodCallException;
use GW\Value\Numberable\Average;
use GW\Value\Numberable\JustNumbers;
use GW\Value\Numberable\Max;
use GW\Value\Numberable\Min;
use GW\Value\Numberable\Sum;
use GW\Value\Numberable\ToScalarNumber;
use Traversable;

final class PlainNumbersArray implements NumbersArray
{
    /** @var ArrayValue<NumberValue> */
    private ArrayValue $numbers;

    /** @param ArrayValue<NumberValue> $numbers */
    public function __construct(ArrayValue $numbers)
    {
        $this->numbers = $numbers;
    }

    /** @param Arrayable<NumberValue> $numbers */
    public static function fromArrayable(Arrayable $numbers): self
    {
        return new self(new PlainArray($numbers));
    }

    /** @param int|float|numeric-string|Numberable ...$numbers */
    public static function just(...$numbers): self
    {
        return self::fromArrayable(new JustNumbers(...$numbers));
    }

    public function sum(): NumberValue
    {
        return new PlainNumber(new Sum($this));
    }

    public function average(): NumberValue
    {
        return new PlainNumber(new Average($this));
    }

    public function min(): NumberValue
    {
        return new PlainNumber(new Min($this));
    }

    public function max(): NumberValue
    {
        return new PlainNumber(new Max($this));
    }

    /** @return int[]|float[] */
    public function toNativeNumbers(): array
    {
        return $this->map(new ToScalarNumber())->toArray();
    }

    public function getIterator(): Traversable
    {
        return $this->numbers->getIterator();
    }

    public function count(): int
    {
        return $this->numbers->count();
    }

    /**
     * @param callable(NumberValue):void $callback
     */
    public function each(callable $callback): NumbersArray
    {
        return new self($this->numbers->each($callback));
    }

    /**
     * @param (callable(NumberValue $valueA, NumberValue $valueB):int)|null $comparator
     */
    public function unique(?callable $comparator = null): NumbersArray
    {
        return new self($this->numbers->unique($comparator));
    }

    /** @return NumberValue[] */
    public function toArray(): array
    {
        return $this->numbers->toArray();
    }

    /**
     * @param callable(NumberValue):bool $filter
     */
    public function filter(callable $filter): NumbersArray
    {
        return new self($this->numbers->filter($filter));
    }

    public function filterEmpty(): NumbersArray
    {
        return new self($this->numbers->filterEmpty());
    }

    /**
     * @template TNewValue
     * @param callable(NumberValue):TNewValue $transformer
     * @return ArrayValue<TNewValue>
     */
    public function map(callable $transformer): ArrayValue
    {
        return $this->numbers->map($transformer);
    }

    /**
     * @template TNewValue
     * @param callable(NumberValue):iterable<TNewValue> $transformer
     * @return ArrayValue<TNewValue>
     */
    public function flatMap(callable $transformer): ArrayValue
    {
        return $this->numbers->flatMap($transformer);
    }

    /**
     * @template TNewKey
     * @param callable(NumberValue):TNewKey $reducer
     * @return AssocValue<TNewKey, NumbersArray>
     * @phpstan-ignore-next-line shrug
     */
    public function groupBy(callable $reducer): AssocValue
    {
        // @phpstan-ignore-next-line shrug
        return $this->numbers
            // @phpstan-ignore-next-line shrug
            ->groupBy($reducer)
            // @phpstan-ignore-next-line shrug
            ->map(static fn(ArrayValue $numbers) => new self($numbers));
    }

    /**
     * @return ArrayValue<array<int, NumberValue>>
     * @phpstan-ignore-next-line shrug
     */
    public function chunk(int $size): ArrayValue
    {
        return $this->numbers->chunk($size);
    }

    public function sort(callable $comparator): NumbersArray
    {
        return new self($this->numbers->sort($comparator));
    }

    public function shuffle(): NumbersArray
    {
        return new self($this->numbers->shuffle());
    }

    public function reverse(): NumbersArray
    {
        return new self($this->numbers->reverse());
    }

    /**
     * @param NumberValue $value
     */
    public function unshift($value): NumbersArray
    {
        return new self($this->numbers->unshift($value));
    }

    /**
     * @param NumberValue|null $value
     */
    public function shift(&$value = null): NumbersArray
    {
        return new self($this->numbers->shift($value));
    }

    /**
     * @param NumberValue $value
     */
    public function push($value): NumbersArray
    {
        return new self($this->numbers->push($value));
    }

    /**
     * @param NumberValue|null $value
     */
    public function pop(&$value = null): NumbersArray
    {
        return new self($this->numbers->pop($value));
    }

    /**
     * @param int $offset
     */
    public function offsetExists($offset): bool
    {
        return $this->numbers->offsetExists($offset);
    }

    /**
     * @param int $offset
     */
    public function offsetGet($offset): NumberValue
    {
        return $this->numbers->offsetGet($offset);
    }

    /**
     * @param int $offset
     * @phpstan-param NumberValue $value
     * @throws BadMethodCallException For immutable types.
     */
    public function offsetSet($offset, $value): void
    {
        $this->numbers->offsetSet($offset, $value);
    }

    /**
     * @param int $offset
     * @throws BadMethodCallException For immutable types.
     */
    public function offsetUnset($offset): void
    {
        $this->numbers->offsetUnset($offset);
    }

    /**
     * @param ArrayValue<NumberValue> $other
     */
    public function join(ArrayValue $other): NumbersArray
    {
        return new self($this->numbers->join($other));
    }

    public function slice(int $offset, int $length): NumbersArray
    {
        return new self($this->numbers->slice($offset, $length));
    }

    /**
     * @param ArrayValue<NumberValue>|null $replacement
     */
    public function splice(int $offset, int $length, ?ArrayValue $replacement = null): NumbersArray
    {
        return new self($this->numbers->splice($offset, $length, $replacement));
    }

    /**
     * @param ArrayValue<NumberValue> $other
     * @param (callable(NumberValue,NumberValue):int)|null $comparator
     */
    public function diff(ArrayValue $other, ?callable $comparator = null): NumbersArray
    {
        return new self($this->numbers->diff($other, $comparator));
    }

    /**
     * @param ArrayValue<NumberValue> $other
     * @param (callable(NumberValue,NumberValue):int)|null $comparator
     */
    public function intersect(ArrayValue $other, ?callable $comparator = null): NumbersArray
    {
        return new self($this->numbers->intersect($other, $comparator));
    }

    /**
     * @template TNewValue
     * @param callable(TNewValue, NumberValue):TNewValue $transformer
     * @param TNewValue $start
     * @return TNewValue
     */
    public function reduce(callable $transformer, $start)
    {
        return $this->numbers->reduce($transformer, $start);
    }

    /**
     * @param callable(NumberValue $reduced, NumberValue $item):NumberValue $transformer
     */
    public function reduceNumber(callable $transformer, NumberValue $start): NumberValue
    {
        return $this->reduce($transformer, $start);
    }

    public function implode(string $glue): StringValue
    {
        return $this->numbers->implode($glue);
    }

    public function notEmpty(): NumbersArray
    {
        return new self($this->numbers->notEmpty());
    }

    /**
     * @return AssocValue<int, NumberValue>
     */
    public function toAssocValue(): AssocValue
    {
        return $this->numbers->toAssocValue();
    }

    public function toStringsArray(): StringsArray
    {
        return $this->numbers->toStringsArray();
    }

    public function toNumbersArray(): NumbersArray
    {
        return $this;
    }

    public function first(): ?NumberValue
    {
        return $this->numbers->first();
    }

    public function last(): ?NumberValue
    {
        return $this->numbers->last();
    }

    /**
     * @param callable(NumberValue):bool $filter
     */
    public function find(callable $filter): ?NumberValue
    {
        return $this->numbers->find($filter);
    }

    /**
     * @param callable(NumberValue):bool $filter
     */
    public function findLast(callable $filter): ?NumberValue
    {
        return $this->numbers->findLast($filter);
    }

    /**
     * @param NumberValue $element
     */
    public function hasElement($element): bool
    {
        return $this->numbers->hasElement($element);
    }

    /**
     * @param callable(NumberValue):bool $filter
     */
    public function any(callable $filter): bool
    {
        return $this->numbers->any($filter);
    }

    /**
     * @param callable(NumberValue):bool $filter
     */
    public function every(callable $filter): bool
    {
        return $this->numbers->every($filter);
    }

    public function isEmpty(): bool
    {
        return $this->numbers->isEmpty();
    }
}
