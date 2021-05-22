<?php declare(strict_types=1);

namespace GW\Value;

interface NumberValue extends Value, Numberable
{
    // comparators

    /**
     * @param int|float|Numberable $other
     * @return int {-1, 0, 1}
     */
    public function compare($other): int;

    /**
     * @param int|float|Numberable $other
     */
    public function equals($other): bool;

    // basic math

    /**
     * @param int|float|Numberable $other
     */
    public function add($other): NumberValue;

    /**
     * @param int|float|Numberable $other
     */
    public function subtract($other): NumberValue;

    /**
     * @param int|float|Numberable $other
     */
    public function multiply($other): NumberValue;

    /**
     * @param int|float|Numberable $other
     */
    public function divide($other): NumberValue;

    public function abs(): NumberValue;

    /**
     * @param int|float|Numberable $divider
     */
    public function modulo($divider): NumberValue;

    // rounding

    public function round(int $precision = 0, ?int $roundMode = null): NumberValue;

    public function floor(): NumberValue;

    public function ceil(): NumberValue;

    /** @param callable(Numberable):Numberable $formula */
    public function calculate(callable $formula): NumberValue;

    // value

    /** @return bool true when 0 or 0.0, false otherwise */
    public function isEmpty(): bool;

    // casting

    public function format(int $decimals = 0, string $separator = '.' , string $thousandsSeparator = ','): StringValue;

    public function toStringValue(): StringValue;

    public function toInteger(): int;

    public function toFloat(): float;

    public function __toString(): string;
}
