<?php declare(strict_types=1);

namespace GW\Value\Arrayable;

use GW\Value\Arrayable;
use function array_merge;

final class Join implements Arrayable
{
    private Arrayable $left;
    private Arrayable $right;

    public function __construct(Arrayable $left, Arrayable $right)
    {
        $this->left = $left;
        $this->right = $right;
    }

    public function toArray(): array
    {
        return array_merge($this->left->toArray(), $this->right->toArray());
    }
}
