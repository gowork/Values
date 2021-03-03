<?php declare(strict_types=1);

namespace GW\Value\Numberable;

use GW\Value\Numberable;

final class Zero implements Numberable
{
    public function toNumber(): int
    {
        return 0;
    }
}
