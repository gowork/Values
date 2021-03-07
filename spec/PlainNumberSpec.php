<?php declare(strict_types=1);

namespace spec\GW\Value;

use DivisionByZeroError;
use GW\Value\Numberable;
use GW\Value\Numberable\Divide;
use GW\Value\Numberable\JustFloat;
use GW\Value\Numberable\JustInteger;
use GW\Value\Numberable\Math;
use GW\Value\Numberable\Add;
use GW\Value\PlainNumber;
use PhpSpec\ObjectBehavior;
use function acos;
use function cos;
use function sin;
use const PHP_ROUND_HALF_DOWN;

final class PlainNumberSpec extends ObjectBehavior
{
    function it_can_be_integer()
    {
        $this->beConstructedWith(new JustInteger(123));

        $this->toNumber()->shouldBe(123);
        $this->toInteger()->shouldBe(123);
        $this->toFloat()->shouldBe(123.0);
    }

    function it_can_be_float()
    {
        $this->beConstructedWith(new JustFloat(123.66));

        $this->toNumber()->shouldBe(123.66);
        $this->toInteger()->shouldBe(123);
        $this->toFloat()->shouldBe(123.66);
    }

    function it_compares_float_and_int_as_equal_just_like_scalars()
    {
        $this->beConstructedWith(new JustInteger(123));

        $this->compare(new JustFloat(123.00))->shouldBe(0);
        $this->equals(new JustFloat(123.00))->shouldBe(true);
    }

    function it_adds_integers()
    {
        $this->beConstructedWith(new JustInteger(123));

        $this->add(new JustInteger(45))->toNumber()->shouldBe(168);
    }

    function it_adds_integer_and_float()
    {
        $this->beConstructedWith(new JustInteger(123));

        $this->add(new JustFloat(.45))->toNumber()->shouldBe(123.45);
    }

    function it_adds_floats()
    {
        $this->beConstructedWith(new JustFloat(.1));

        $this->add(new JustFloat(.1))->toNumber()->shouldBe(.2);
    }

    function it_adds_float_and_integer()
    {
        $this->beConstructedWith(new JustFloat(.1));

        $this->add(new JustInteger(123))->toNumber()->shouldBe(123.1);
    }

    function it_subtracts_integers()
    {
        $this->beConstructedWith(new JustInteger(123));

        $this->subtract(new JustInteger(45))->toNumber()->shouldBe(78);
    }

    function it_subtracts_integer_and_float()
    {
        $this->beConstructedWith(new JustInteger(123));

        $this->subtract(new JustFloat(.45))->toNumber()->shouldBe(122.55);
    }

    function it_subtracts_floats()
    {
        $this->beConstructedWith(new JustFloat(.2));

        $this->subtract(new JustFloat(.1))->toNumber()->shouldBe(.1);
    }

    function it_subtracts_float_and_integer()
    {
        $this->beConstructedWith(new JustFloat(123.5));

        $this->subtract(new JustInteger(23))->toNumber()->shouldBe(100.5);
    }

    function it_multiplies_integers()
    {
        $this->beConstructedWith(new JustInteger(8));

        $this->multiply(new JustInteger(9))->toNumber()->shouldBe(72);
    }

    function it_multiplies_integer_and_float()
    {
        $this->beConstructedWith(new JustInteger(8));

        $this->multiply(new JustFloat(2.4))->toNumber()->shouldBe(19.2);
    }

    function it_multiplies_floats()
    {
        $this->beConstructedWith(new JustFloat(.4));

        $this->multiply(new JustFloat(.5))->toNumber()->shouldBe(.2);
    }

    function it_multiplies_float_and_integer()
    {
        $this->beConstructedWith(new JustFloat(11.2));

        $this->multiply(new JustInteger(4))->toNumber()->shouldBe(44.8);
    }

    function it_divides_integers()
    {
        $this->beConstructedWith(new JustInteger(12));

        $this->divide(new JustInteger(4))->toNumber()->shouldBe(3);
    }

    function it_divides_integers_returning_float_when_fraction_result()
    {
        $this->beConstructedWith(new JustInteger(12));

        $this->divide(new JustInteger(5))->toNumber()->shouldBe(2.4);
    }

    function it_throws_error_when_dividing_by_zero()
    {
        $this->beConstructedThrough(
            fn() => (new PlainNumber(new JustInteger(12)))->divide(new JustInteger(0))
        );

        $this->shouldThrow(DivisionByZeroError::class)->during('toNumber');
    }

    function it_divides_integer_and_float_returning_float()
    {
        $this->beConstructedWith(new JustInteger(12));

        $this->divide(new JustFloat(.5))->toNumber()->shouldBe(24.0);
    }

    function it_divides_floats()
    {
        $this->beConstructedWith(new JustFloat(.12));

        $this->divide(new JustFloat(.04))->toNumber()->shouldBe(3.0);
    }

    function it_divides_float_and_integer()
    {
        $this->beConstructedWith(new JustFloat(12.5));

        $this->divide(new JustInteger(5))->toNumber()->shouldBe(2.5);
    }

    function it_calculates_modulo_of_integer()
    {
        $this->beConstructedWith(new JustInteger(12));

        $this->modulo(new JustInteger(11))->toNumber()->shouldBe(1);
        $this->modulo(new JustInteger(7))->toNumber()->shouldBe(5);
    }

    function it_calculates_modulo_of_float_just_like_php_does()
    {
        $this->beConstructedWith(new JustInteger(12));

        $this->modulo(new JustFloat(11.9))->toNumber()->shouldBe(1);
    }

    function it_throws_error_when_modulo_divider_is_zero()
    {
        $this->beConstructedThrough(
            fn() => (new PlainNumber(new JustInteger(12)))->modulo(new JustInteger(0))
        );

        $this->shouldThrow(DivisionByZeroError::class)->during('toNumber');
    }

    function it_absolutes_positive_integer()
    {
        $this->beConstructedWith(new JustInteger(2));

        $this->abs()->toNumber()->shouldBe(2);
    }

    function it_absolutes_negative_integer()
    {
        $this->beConstructedWith(new JustInteger(-2));

        $this->abs()->toNumber()->shouldBe(2);
    }

    function it_absolutes_positive_float()
    {
        $this->beConstructedWith(new JustFloat(12.3));

        $this->abs()->toNumber()->shouldBe(12.3);
    }

    function it_absolutes_negative_float()
    {
        $this->beConstructedWith(new JustFloat(-12.3));

        $this->abs()->toNumber()->shouldBe(12.3);
    }

    function it_rounds_integer()
    {
        $this->beConstructedWith(new JustInteger(123));

        $this->round(-2)->toNumber()->shouldBe(100.0);
    }

    function it_rounds_float()
    {
        $this->beConstructedWith(new JustFloat(12.3));

        $this->round()->toNumber()->shouldBe(12.0);
    }

    function it_rounds_float_half_up()
    {
        $this->beConstructedWith(new JustFloat(12.5));

        $this->round()->toNumber()->shouldBe(13.0);
    }

    function it_rounds_float_half_down()
    {
        $this->beConstructedWith(new JustFloat(12.5));

        $this->round(0, PHP_ROUND_HALF_DOWN)->toNumber()->shouldBe(12.0);
    }

    function it_floors_integer_to_float()
    {
        $this->beConstructedWith(new JustInteger(2));

        $this->floor()->toNumber()->shouldBe(2.0);
    }

    function it_floors_float()
    {
        $this->beConstructedWith(new JustFloat(2.9));

        $this->floor()->toNumber()->shouldBe(2.0);
    }

    function it_ceil_integer_to_float()
    {
        $this->beConstructedWith(new JustInteger(2));

        $this->ceil()->toNumber()->shouldBe(2.0);
    }

    function it_ceil_float()
    {
        $this->beConstructedWith(new JustFloat(2.1));

        $this->ceil()->toNumber()->shouldBe(3.0);
    }

    function it_is_empty_when_zero_integer()
    {
        $this->beConstructedWith(new JustInteger(0));

        $this->isEmpty()->shouldBe(true);
    }

    function it_is_empty_when_zero_float()
    {
        $this->beConstructedWith(new Add(new JustFloat(-1.0), new JustFloat(1.0)));

        $this->isEmpty()->shouldBe(true);
    }

    function it_calculates_custom_formula()
    {
        $this->beConstructedWith(new JustInteger(100));

        $formula = fn(Numberable $number): Numberable => new Divide(
            new Add($number, new JustInteger(700)),
            new JustInteger(2)
        );

        $this->calculate($formula)->toNumber()->shouldBe(400);
    }

    function it_calculates_math_formulas()
    {
        $this->beConstructedWith(new JustInteger(90));

        $this->calculate(Math::cos())->toNumber()->shouldBe(cos(90));
        $this->divide(new JustInteger(100))->calculate(Math::acos())->toNumber()->shouldBe(acos(.90));
        $this->calculate(Math::sin())->toNumber()->shouldBe(sin(90));
        $this->divide(new JustInteger(100))->calculate(Math::asin())->toNumber()->shouldBe(asin(.90));
        $this->calculate(Math::tan())->toNumber()->shouldBe(tan(90));
        $this->divide(new JustInteger(100))->calculate(Math::atan())->toNumber()->shouldBe(atan(.90));
    }
}
