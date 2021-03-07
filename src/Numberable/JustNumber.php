<?php declare(strict_types=1);

namespace GW\Value\Numberable;

use GW\Value\Numberable;

final class JustNumber implements Numberable
{
    /** @var int|float */
    private $number;

    /** @param int|float $number */
    public function __construct($number)
    {
        $this->number = $number;
    }

    /** @return int|float */
    public function toNumber()
    {
        return $this->number;
    }
}
