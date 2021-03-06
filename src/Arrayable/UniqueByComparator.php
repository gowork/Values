<?php declare(strict_types=1);

namespace GW\Value\Arrayable;

use GW\Value\Arrayable;

/**
 * @template TValue
 * @implements Arrayable<TValue>
 */
final class UniqueByComparator implements Arrayable
{
    /** @var Arrayable<TValue> */
    private Arrayable $arrayable;
    /** @var callable(TValue,TValue):int */
    private $comparator;

    /**
     * @param Arrayable<TValue> $arrayable
     * @param callable(TValue,TValue):int $comparator
     */
    public function __construct(Arrayable $arrayable, callable $comparator)
    {
        $this->arrayable = $arrayable;
        $this->comparator = $comparator;
    }

    /** @return TValue[] */
    public function toArray(): array
    {
        $result = [];
        $comparator = $this->comparator;

        foreach ($this->arrayable->toArray() as $valueA) {
            foreach ($result as $valueB) {
                if ($comparator($valueA, $valueB) === 0) {
                    // item already in result
                    continue 2;
                }
            }

            $result[] = $valueA;
        }

        return $result;
    }
}
