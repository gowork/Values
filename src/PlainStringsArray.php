<?php declare(strict_types=1);

namespace GW\Value;

use GW\Value\Numberable\ToNumberValue;
use GW\Value\Stringable\ToNativeString;
use GW\Value\Stringable\ToStringValue;
use Traversable;
use function in_array;

final class PlainStringsArray implements StringsArray
{
    /** @var ArrayValue<StringValue> */
    private ArrayValue $strings;

    /**
     * @param ArrayValue<mixed> $strings
     */
    public function __construct(ArrayValue $strings)
    {
        $this->strings = $this->mapStringValues($strings);
    }

    /**
     * @param array<mixed> $strings
     */
    public static function fromArray(array $strings): self
    {
        return new self(Wrap::array($strings));
    }

    /**
     * @param StringsArray $other
     */
    public function join(ArrayValue $other): PlainStringsArray
    {
        return new self($this->strings->join($this->mapStringValues($other)));
    }

    public function slice(int $offset, int $length): PlainStringsArray
    {
        return new self($this->strings->slice($offset, $length));
    }

    /**
     * @param StringsArray $replacement
     */
    public function splice(int $offset, int $length, ?ArrayValue $replacement = null): PlainStringsArray
    {
        return new self($this->strings->splice($offset, $length, $replacement));
    }

    /**
     * @param StringsArray $other
     * @param (callable(StringValue $valueA, StringValue $valueB):int)|null $comparator
     */
    public function diff(ArrayValue $other, ?callable $comparator = null): PlainStringsArray
    {
        return new self($this->strings->diff($this->mapStringValues($other), $comparator));
    }

    /**
     * @param StringsArray $other
     * @param callable(StringValue $valueA, StringValue $valueB):int|null $comparator
     */
    public function intersect(ArrayValue $other, ?callable $comparator = null): PlainStringsArray
    {
        return new self($this->strings->intersect($this->mapStringValues($other), $comparator));
    }

    /**
     * @template TNewValue
     * @param callable(TNewValue $reduced, StringValue $value):TNewValue $transformer
     * @phpstan-param TNewValue $start
     * @phpstan-return TNewValue
     */
    public function reduce(callable $transformer, $start)
    {
        return $this->strings->reduce($transformer, $start);
    }

    /**
     * @param callable(StringValue $value):StringValue $transformer
     */
    public function map(callable $transformer): PlainStringsArray
    {
        return new self($this->strings->map($transformer));
    }

    /**
     * @param callable(StringValue $value):iterable<StringValue> $transformer
     */
    public function flatMap(callable $transformer): PlainStringsArray
    {
        return new self($this->strings->flatMap($transformer));
    }

    /**
     * @param callable(StringValue $value):string $reducer
     * @phpstan-return AssocValue<string, ArrayValue<StringValue>>
     * @phpstan-ignore-next-line shrug
     */
    public function groupBy(callable $reducer): AssocValue
    {
        // @phpstan-ignore-next-line shrug
        return $this->strings
            ->groupBy($reducer)
            // @phpstan-ignore-next-line shrug
            ->map(
                /** @return ArrayValue<StringValue> */
                static fn(ArrayValue $value): ArrayValue => $value->toStringsArray()
            );
    }

    /**
     * @return ArrayValue<array<int, StringValue>>
     * @phpstan-ignore-next-line shrug
     */
    public function chunk(int $size): ArrayValue
    {
        return Wrap::array($this->strings->chunk($size)->toArray());
    }

    /**
     * @param callable(StringValue $value): bool $filter
     */
    public function filter(callable $filter): PlainStringsArray
    {
        return new self($this->strings->filter($filter));
    }

    public function implode(string $glue): StringValue
    {
        return $this->strings->implode($glue);
    }

    public function first(): ?StringValue
    {
        return $this->strings->first();
    }

    public function last(): ?StringValue
    {
        return $this->strings->last();
    }

    /**
     * @param callable(StringValue $value): bool $filter
     */
    public function find(callable $filter): ?StringValue
    {
        return $this->strings->find($filter);
    }

    /**
     * @param callable(StringValue $value): bool $filter
     */
    public function findLast(callable $filter): ?StringValue
    {
        return $this->strings->findLast($filter);
    }

    /**
     * @param StringValue $element
     */
    public function hasElement($element): bool
    {
        return in_array($element, $this->strings->toArray(), false);
    }

    /**
     * @param callable(StringValue $value): bool $filter
     */
    public function any(callable $filter): bool
    {
        return $this->strings->any($filter);
    }

    /**
     * @param callable(StringValue $value): bool $filter
     */
    public function every(callable $filter): bool
    {
        return $this->strings->every($filter);
    }

    /**
     * @param callable(StringValue $value): void $callback
     */
    public function each(callable $callback): PlainStringsArray
    {
        $this->strings->each($callback);

        return $this;
    }

    /**
     * @param (callable(StringValue $valueA, StringValue $valueB):int)|null $comparator
     */
    public function unique(?callable $comparator = null): PlainStringsArray
    {
        return new self($this->strings->unique($comparator));
    }

    /**
     * @return array<int, StringValue>
     */
    public function toArray(): array
    {
        return $this->strings->toArray();
    }

    /**
     * @return string[]
     */
    public function toNativeStrings(): array
    {
        return $this->strings
            ->map(new ToNativeString())
            ->toArray();
    }

    public function filterEmpty(): PlainStringsArray
    {
        return new self($this->strings->filterEmpty());
    }

    public function sort(callable $comparator): PlainStringsArray
    {
        return new self($this->strings->sort($comparator));
    }

    public function shuffle(): PlainStringsArray
    {
        return new self($this->strings->shuffle());
    }

    public function reverse(): PlainStringsArray
    {
        return new self($this->strings->reverse());
    }

    /**
     * @return Traversable<int, StringValue>
     */
    public function getIterator(): Traversable
    {
        return $this->strings->getIterator();
    }

    /**
     * @param int $offset
     */
    public function offsetExists($offset): bool
    {
        return $this->strings->offsetExists($offset);
    }

    /**
     * @param int $offset
     */
    public function offsetGet($offset): StringValue
    {
        return $this->strings->offsetGet($offset);
    }

    /**
     * @param int $offset
     * @param StringValue $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->strings->offsetSet($offset, $value);
    }

    /**
     * @param int $offset
     */
    public function offsetUnset($offset): void
    {
        $this->strings->offsetUnset($offset);
    }

    public function count(): int
    {
        return $this->strings->count();
    }

    /**
     * @param StringValue|string $value
     */
    public function unshift($value): PlainStringsArray
    {
        return new self($this->strings->unshift(Wrap::string($value)));
    }

    /**
     * @param StringValue|null $value
     */
    public function shift(&$value = null): PlainStringsArray
    {
        return new self($this->strings->shift($value));
    }

    /**
     * @param StringValue|string $value
     */
    public function push($value): PlainStringsArray
    {
        return new self($this->strings->push(Wrap::string($value)));
    }

    /**
     * @param StringValue|null $value
     */
    public function pop(&$value = null): PlainStringsArray
    {
        return new self($this->strings->pop($value));
    }

    /**
     * @param callable(string $value):(StringValue|string) $transformer
     */
    public function transform(callable $transformer): PlainStringsArray
    {
        return $this->map(fn(StringValue $item): StringValue => Wrap::string($item->transform($transformer)));
    }

    public function stripTags(): PlainStringsArray
    {
        return $this->map(fn(StringValue $item): StringValue => $item->stripTags());
    }

    /**
     * @param string|StringValue $characterMask
     */
    public function trim($characterMask = self::TRIM_MASK): PlainStringsArray
    {
        return $this->map(fn(StringValue $item): StringValue => $item->trim($characterMask));
    }

    /**
     * @param string|StringValue $characterMask
     */
    public function trimRight($characterMask = self::TRIM_MASK): PlainStringsArray
    {
        return $this->map(fn(StringValue $item): StringValue => $item->trimRight($characterMask));
    }

    /**
     * @param string|StringValue $characterMask
     */
    public function trimLeft($characterMask = self::TRIM_MASK): PlainStringsArray
    {
        return $this->map(fn(StringValue $item): StringValue => $item->trimLeft($characterMask));
    }

    public function lower(): PlainStringsArray
    {
        return $this->map(fn(StringValue $item): StringValue => $item->lower());
    }

    public function upper(): PlainStringsArray
    {
        return $this->map(fn(StringValue $item): StringValue => $item->upper());
    }

    public function lowerFirst(): PlainStringsArray
    {
        return $this->map(fn(StringValue $item): StringValue => $item->lowerFirst());
    }

    public function upperFirst(): PlainStringsArray
    {
        return $this->map(fn(StringValue $item): StringValue => $item->upperFirst());
    }

    public function upperWords(): PlainStringsArray
    {
        return $this->map(fn(StringValue $item): StringValue => $item->upperWords());
    }

    /**
     * @param string|StringValue $string
     */
    public function padRight(int $length, $string = ' '): PlainStringsArray
    {
        return $this->map(fn(StringValue $item): StringValue => $item->padRight($length, $string));
    }

    /**
     * @param string|StringValue $string
     */
    public function padLeft(int $length, $string = ' '): PlainStringsArray
    {
        return $this->map(fn(StringValue $item): StringValue => $item->padLeft($length, $string));
    }

    /**
     * @param string|StringValue $string
     */
    public function padBoth(int $length, $string = ' '): PlainStringsArray
    {
        return $this->map(fn(StringValue $item): StringValue => $item->padBoth($length, $string));
    }

    /**
     * @param string|StringValue $search
     * @param string|StringValue $replace
     */
    public function replace($search, $replace): PlainStringsArray
    {
        return $this->map(fn(StringValue $item): StringValue => $item->replace($search, $replace));
    }

    /**
     * @param array<int,string>|ArrayValue<string> $search
     * @param string|StringValue $replace
     */
    public function replaceAll($search, $replace): PlainStringsArray
    {
        return $this->map(fn(StringValue $item): StringValue => $item->replaceAll($search, $replace));
    }

    /**
     * @param array<string,string>|AssocValue<string,string> $pairs
     */
    public function replacePairs($pairs): PlainStringsArray
    {
        return $this->map(fn(StringValue $item): StringValue => $item->replacePairs($pairs));
    }

    /**
     * @param string|StringValue $pattern
     * @param string|StringValue $replacement
     */
    public function replacePattern($pattern, $replacement): PlainStringsArray
    {
        return $this->map(fn(StringValue $item): StringValue => $item->replacePattern($pattern, $replacement));
    }

    /**
     * @param string|StringValue $pattern
     */
    public function replacePatternCallback($pattern, callable $callback): PlainStringsArray
    {
        return $this->map(fn(StringValue $item): StringValue => $item->replacePatternCallback($pattern, $callback));
    }

    /**
     * @param string|StringValue $postfix
     */
    public function truncate(int $length, $postfix = '...'): PlainStringsArray
    {
        return $this->map(fn(StringValue $item): StringValue => $item->truncate($length, $postfix));
    }

    public function substring(int $start, ?int $length = null): PlainStringsArray
    {
        return $this->map(fn(StringValue $item): StringValue => $item->substring($start, $length));
    }

    /**
     * @param string|StringValue $other
     */
    public function postfix($other): PlainStringsArray
    {
        return $this->map(fn(StringValue $item): StringValue => $item->postfix($other));
    }

    /**
     * @param string|StringValue $other
     */
    public function prefix($other): PlainStringsArray
    {
        return $this->map(fn(StringValue $item): StringValue => $item->prefix($other));
    }

    public function length(): int
    {
        return $this->toStringValue()->length();
    }

    /**
     * @param string|StringValue $needle
     */
    public function position($needle): ?int
    {
        return $this->toStringValue()->position($needle);
    }

    /**
     * @param string|StringValue $needle
     */
    public function positionLast($needle): ?int
    {
        return $this->toStringValue()->positionLast($needle);
    }

    /**
     * @param string|StringValue $pattern
     * @return ArrayValue<string[][]>
     */
    public function matchAllPatterns($pattern): ArrayValue
    {
        return $this->toStringValue()->matchAllPatterns($pattern);
    }

    /**
     * @param string|StringValue $pattern
     * @return StringsArray
     */
    public function matchPatterns($pattern): StringsArray
    {
        return $this->toStringValue()->matchPatterns($pattern);
    }

    /**
     * @param string|StringValue $pattern
     */
    public function isMatching($pattern): bool
    {
        return $this->toStringValue()->isMatching($pattern);
    }

    /**
     * @param string|StringValue $pattern
     */
    public function startsWith($pattern): bool
    {
        return $this->toStringValue()->startsWith($pattern);
    }

    /**
     * @param string|StringValue $pattern
     */
    public function endsWith($pattern): bool
    {
        return $this->toStringValue()->endsWith($pattern);
    }

    /**
     * @param string|StringValue $pattern
     */
    public function splitByPattern($pattern): StringsArray
    {
        return $this->toStringValue()->splitByPattern($pattern);
    }

    /**
     * @param string|StringValue $delimiter
     */
    public function explode($delimiter): StringsArray
    {
        return $this->toStringValue()->explode($delimiter);
    }

    /**
     * @param string|StringValue $substring
     */
    public function contains($substring): bool
    {
        return $this->toStringValue()->contains($substring);
    }

    public function toString(): string
    {
        return $this->toStringValue()->toString();
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function notEmpty(): PlainStringsArray
    {
        return $this->filter(Filters::notEmpty());
    }

    /**
     * @param ArrayValue<mixed> $strings
     * @return ArrayValue<StringValue>
     */
    private function mapStringValues(ArrayValue $strings): ArrayValue
    {
        return $strings->map(new ToStringValue());
    }

    private function toStringValue(): StringValue
    {
        return $this->implode(' ');
    }

    public function isEmpty(): bool
    {
        return $this->strings->isEmpty() || $this->toStringValue()->isEmpty();
    }

    /**
     * @return ArrayValue<StringValue>
     */
    public function toArrayValue(): ArrayValue
    {
        return $this->strings;
    }

    /**
     * @return AssocValue<int, StringValue>
     */
    public function toAssocValue(): AssocValue
    {
        return $this->strings->toAssocValue();
    }

    public function toStringsArray(): self
    {
        return $this;
    }

    public function toNumbersArray(): NumbersArray
    {
        return new PlainNumbersArray($this->strings->map(new ToNativeString())->map(new ToNumberValue()));
    }
}
