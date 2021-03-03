<?php declare(strict_types=1);

namespace GW\Value;

use GW\Value\Arrayable\Cache;
use GW\Value\Arrayable\JustArray;
use function is_array;

/**
 * @template TValue
 * @extends GenericArray<TValue>
 */
final class PlainArray extends GenericArray
{
    /** @phpstan-var Arrayable<TValue> */
    private Arrayable $items;

    /**
     * @phpstan-param array<mixed, TValue>|Arrayable<TValue> $items
     */
    public function __construct($items)
    {
        $this->items = is_array($items) ? new JustArray($items) : new Cache($items);
    }

    /** @return Arrayable<TValue> */
    public function items(): Arrayable
    {
        return $this->items;
    }

    /**
     * @param Arrayable<TValue> $items
     * @return PlainArray<TValue>
     */
    public static function new(Arrayable $items): PlainArray
    {
        return new self($items);
    }
}
