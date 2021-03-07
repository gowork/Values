<?php declare(strict_types=1);

namespace GW\Value;

use GW\Value\Numberable\Absolute;
use GW\Value\Numberable\Add;
use GW\Value\Numberable\Ceil;
use GW\Value\Numberable\Divide;
use GW\Value\Numberable\Floor;
use GW\Value\Numberable\JustInteger;
use GW\Value\Numberable\JustNumber;
use GW\Value\Numberable\Modulo;
use GW\Value\Numberable\Multiply;
use GW\Value\Numberable\Round;
use GW\Value\Numberable\Subtract;
use function is_int;
use function number_format;
use const PHP_FLOAT_DIG;

final class PlainNumber implements NumberValue
{
    private Numberable $number;

    public function __construct(Numberable $number)
    {
        $this->number = $number;
    }

    /** @param int|float $number */
    public static function from($number): self
    {
        return new self(new JustNumber($number));
    }

    public function format(int $decimals = 0, string $separator = '.', string $thousandsSeparator = ','): StringValue
    {
        return Wrap::string(number_format($this->number->toNumber(), $decimals, $separator, $thousandsSeparator));
    }

    public function compare(Numberable $other): int
    {
        return $this->toNumber() <=> $other->toNumber();
    }

    public function equals(Numberable $other): bool
    {
        return $this->compare($other) === 0;
    }

    public function add(Numberable $other): NumberValue
    {
        return new self(new Add($this->number, $other));
    }

    public function subtract(Numberable $other): NumberValue
    {
        return new self(new Subtract($this->number, $other));
    }

    public function multiply(Numberable $other): NumberValue
    {
        return new self(new Multiply($this->number, $other));
    }

    public function divide(Numberable $other): NumberValue
    {
        return new self(new Divide($this->number, $other));
    }

    public function modulo(Numberable $divider): NumberValue
    {
        return new self(new Modulo($this->number, $divider));
    }

    public function abs(): NumberValue
    {
        return new self(new Absolute($this->number));
    }

    public function round(int $precision = 0, ?int $roundMode = null): NumberValue
    {
        return new self(new Round($this->number, $precision, $roundMode));
    }

    public function floor(): NumberValue
    {
        return new self(new Floor($this->number));
    }

    public function ceil(): NumberValue
    {
        return new self(new Ceil($this->number));
    }

    /** @param callable(Numberable):Numberable $formula */
    public function calculate(callable $formula): NumberValue
    {
        return new self($formula($this->number));
    }

    public function isEmpty(): bool
    {
        return $this->toFloat() === 0.0;
    }

    /** @return int|float */
    public function toNumber()
    {
        return $this->number->toNumber();
    }

    public function toInteger(): int
    {
        return (int)$this->number->toNumber();
    }

    public function toFloat(): float
    {
        return (float)$this->number->toNumber();
    }

    public function toStringValue(): StringValue
    {
        $number = $this->number->toNumber();
        if (is_int($number)) {
            return Wrap::string((string)$number);
        }

        $value = $this->format(PHP_FLOAT_DIG, '.', '')->trimRight('0');

        return $value->endsWith('.') ? $value->postfix('0') : $value;
    }

    public function __toString(): string
    {
        return $this->toStringValue()->toString();
    }
}
