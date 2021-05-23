<?php declare(strict_types=1);

namespace GW\Value\Numberable;

use GW\Value\Numberable;

final class JustNumber implements Numberable
{
    /** @var int|float */
    private $number;

    /** @param int|float|numeric-string $number */
    public function __construct($number)
    {
        $this->number = $number + 0;
    }

    /** @param int|float|numeric-string|Numberable $number */
    public static function wrap($number): Numberable
    {
        return $number instanceof Numberable ? $number : new self($number);
    }

    /** @return int|float */
    public function toNumber()
    {
        return $this->number + 0;
    }
}
