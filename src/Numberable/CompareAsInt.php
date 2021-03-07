<?php declare(strict_types=1);

namespace GW\Value\Numberable;

use GW\Value\Numberable;

final class CompareAsInt
{
    private int $direction;

    private function __construct(int $direction)
    {
        $this->direction = $direction;
    }

    public static function asc(): self
    {
        return new self(1);
    }

    public static function desc(): self
    {
        return new self(-1);
    }

    public function __invoke(Numberable $left, Numberable $right): int
    {
        return $this->direction * ((int)$left->toNumber() <=> (int)$right->toNumber());
    }
}
