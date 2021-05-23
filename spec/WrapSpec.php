<?php

namespace spec\GW\Value;

use GW\Value\Arrayable\JustArray;
use GW\Value\ArrayValue;
use GW\Value\AssocValue;
use GW\Value\Numberable\JustInteger;
use GW\Value\NumbersArray;
use GW\Value\NumberValue;
use GW\Value\PlainNumber;
use GW\Value\PlainNumbersArray;
use GW\Value\StringsArray;
use GW\Value\StringValue;
use PhpSpec\ObjectBehavior;

final class WrapSpec extends ObjectBehavior
{
    function it_should_return_ArrayValue_instance_when_initialized_through_array()
    {
        $this->beConstructedThrough('array', [[]]);
        $this->shouldHaveType(ArrayValue::class);
    }

    function it_should_return_ArrayValue_instance_when_initialized_through_createFromValues()
    {
        $this->beConstructedThrough('arrayFromValues', ['a', 'b', 'c']);
        $this->shouldHaveType(ArrayValue::class);
    }

    function it_should_return_AssocArray_instance_when_initialized_through_assocArray()
    {
        $this->beConstructedThrough('assocArray', [[]]);
        $this->shouldHaveType(AssocValue::class);
    }

    function it_should_return_StringValue_when_initialized_through_string()
    {
        $this->beConstructedThrough('string', ['string']);
        $this->shouldHaveType(StringValue::class);
    }

    function it_should_return_StringsArray_when_initialized_through_stringsArray()
    {
        $this->beConstructedThrough('stringsArray', [['a', 'b', 'c']]);
        $this->shouldHaveType(StringsArray::class);
    }

    function it_should_return_NumberValue_of_scalar_number()
    {
        $this->beConstructedThrough('number', [123]);
        $this->shouldHaveType(NumberValue::class);
    }

    function it_should_return_NumberValue_of_Numberable()
    {
        $this->beConstructedThrough('number', [new JustInteger(123)]);
        $this->shouldHaveType(NumberValue::class);
    }

    function it_should_return_NumberValue_of_itself()
    {
        $this->beConstructedThrough('number', [PlainNumber::from(123)]);
        $this->shouldHaveType(NumberValue::class);
    }

    function it_should_return_NumbersArray_of_scalar_numbers()
    {
        $this->beConstructedThrough('numbersArray', [[1, 2, 3.0]]);
        $this->shouldHaveType(NumbersArray::class);
    }

    function it_should_return_NumbersArray_of_numberable()
    {
        $this->beConstructedThrough('numbersArray', [new JustArray([1, 2, 3.0])]);
        $this->shouldHaveType(NumbersArray::class);
    }

    function it_should_return_NumbersArray_of_itself()
    {
        $this->beConstructedThrough('numbersArray', [PlainNumbersArray::just(1, 2, 3.0)]);
        $this->shouldHaveType(NumbersArray::class);
    }
}
