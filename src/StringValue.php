<?php declare(strict_types=1);

namespace GW\Value;

interface StringValue extends Value
{
    public const TRIM_MASK = " \t\n\r\0\x0B";

    /**
     * @param callable(string $value):(StringValue|string) $transformer
     * @return StringValue
     */
    public function transform(callable $transformer): StringValue;

    /**
     * @return StringValue
     */
    public function stripTags(): StringValue;

    /**
     * @return StringValue
     * @param string|StringValue $characterMask
     */
    public function trim($characterMask = self::TRIM_MASK): StringValue;

    /**
     * @return StringValue
     * @param string|StringValue $characterMask
     */
    public function trimRight($characterMask = self::TRIM_MASK): StringValue;

    /**
     * @return StringValue
     * @param string|StringValue $characterMask
     */
    public function trimLeft($characterMask = self::TRIM_MASK): StringValue;

    /**
     * @return StringValue
     */
    public function lower(): StringValue;

    /**
     * @return StringValue
     */
    public function upper(): StringValue;

    /**
     * @return StringValue
     */
    public function lowerFirst(): StringValue;

    /**
     * @return StringValue
     */
    public function upperFirst(): StringValue;

    /**
     * @return StringValue
     */
    public function upperWords(): StringValue;

    /**
     * @return StringValue
     * @param string|StringValue $string
     */
    public function padRight(int $length, $string = ' '): StringValue;

    /**
     * @return StringValue
     * @param string|StringValue $string
     */
    public function padLeft(int $length, $string = ' '): StringValue;

    /**
     * @return StringValue
     * @param string|StringValue $string
     */
    public function padBoth(int $length, $string = ' '): StringValue;

    /**
     * @return StringValue
     * @param string|StringValue $search
     * @param string|StringValue $replace
     */
    public function replace($search, $replace): StringValue;

    /**
     * @return StringValue
     * @param array<int,string>|ArrayValue<string> $search
     * @param string|StringValue $replace
     */
    public function replaceAll($search, $replace): StringValue;

    /**
     * @return StringValue
     * @param array<string,string>|AssocValue<string,string> $pairs
     */
    public function replacePairs($pairs): StringValue;

    /**
     * @return StringValue
     * @param string|StringValue $pattern
     * @param string|StringValue $replacement
     */
    public function replacePattern($pattern, $replacement): StringValue;

    /**
     * @return StringValue
     * @param string|StringValue $pattern
     */
    public function replacePatternCallback($pattern, callable $callback): StringValue;

    /**
     * @return StringValue
     * @param string|StringValue $postfix
     */
    public function truncate(int $length, $postfix = '...'): StringValue;

    /**
     * @return StringValue
     */
    public function substring(int $start, ?int $length = null): StringValue;

    /**
     * @return StringValue
     * @param string|StringValue $other
     */
    public function postfix($other): StringValue;

    /**
     * @return StringValue
     * @param string|StringValue $other
     */
    public function prefix($other): StringValue;

    public function length(): int;

    /**
     * @param string|StringValue $needle
     */
    public function position($needle): ?int;

    /**
     * @param string|StringValue $needle
     */
    public function positionLast($needle): ?int;

    /**
     * @param string|StringValue $pattern
     * @return ArrayValue<array<int, string>>
     */
    public function matchAllPatterns($pattern): ArrayValue;

    /**
     * @param string|StringValue $pattern
     * @return StringsArray
     */
    public function matchPatterns($pattern): StringsArray;

    /**
     * @param string|StringValue $pattern
     */
    public function isMatching($pattern): bool;

    /**
     * @param string|StringValue $pattern
     */
    public function startsWith($pattern): bool;

    /**
     * @param string|StringValue $pattern
     */
    public function endsWith($pattern): bool;

    /**
     * @return StringsArray
     * @param string|StringValue $pattern
     */
    public function splitByPattern($pattern): StringsArray;

    /**
     * @return StringsArray
     * @param string|StringValue $delimiter
     */
    public function explode($delimiter): StringsArray;

    /**
     * @param string|StringValue $substring
     */
    public function contains($substring): bool;

    public function toString(): string;

    public function __toString(): string;
}
