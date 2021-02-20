<?php declare(strict_types=1);

namespace GW\Value;

/**
 * @internal
 * @template TKey
 * @template TValue
 */
final class IterableValueStack
{
    /** @phpstan-var IterableValueIterator<TKey, TValue> */
    private IterableValueIterator $iterable;

    /** @phpstan-var array<int, (callable(iterable<TKey,TValue>): iterable<mixed,mixed>)> */
    private array $modifiers = [];

    /**
     * @phpstan-param IterableValueIterator<TKey, TValue> $iterable
     */
    public function __construct(IterableValueIterator $iterable)
    {
        $this->iterable = $iterable;
    }

    /**
     * @template TNewKey
     * @template TNewValue
     * @param callable(iterable<TKey,TValue>):iterable<TNewKey,TNewValue> $modifier
     * @phpstan-return IterableValueStack<TNewKey, TNewValue>
     */
    public function push(callable $modifier): self
    {
        $clone = clone $this;
        $clone->modifiers[] = $modifier;

        return $clone;
    }

    /**
     * @phpstan-param array<int, (callable(iterable<TKey,TValue>): iterable<TKey,TValue>)> $modifiers
     * @phpstan-param iterable<TKey, TValue> $iterable
     * @phpstan-return iterable<TKey, TValue>
     */
    private function next(array &$modifiers, iterable &$iterable, int $i): iterable
    {
        if (!isset($modifiers[$i])) {
            yield from $iterable;

            return;
        }

        yield from $modifiers[$i--]($this->next($modifiers, $iterable, $i));
    }

    /**
     * @phpstan-return iterable<TKey, TValue>
     */
    public function iterate(): iterable
    {
        $iterable = ($this->iterable)();
        $modifiers = $this->modifiers;
        $i = \count($modifiers) - 1;

        yield from $this->next($modifiers, $iterable, $i);
    }
}
