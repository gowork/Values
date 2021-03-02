<?php declare(strict_types=1);

namespace GW\Value\Numberable;

use GW\Value\Numberable;

final class Math
{
    /** @var callable(int|float):int|float */
    private $fn;

    /** @param callable(int|float):int|float */
    public function __construct(callable $fn)
    {
        $this->fn = $fn;
    }

    public static function cos(): self
    {
        return new self('cos');
    }

    public static function acos(): self
    {
        return new self('acos');
    }

    public static function sin(): self
    {
        return new self('sin');
    }

    public static function asin(): self
    {
        return new self('asin');
    }

    public static function tan(): self
    {
        return new self('tan');
    }

    public static function atan(): self
    {
        return new self('atan');
    }

    public static function exp(): self
    {
        return new self('exp');
    }

    public static function sqrt(): self
    {
        return new self('sqrt');
    }

    public function __invoke(Numberable $numberable): Numberable
    {
        return new Formula($numberable, $this->fn);
    }
}
