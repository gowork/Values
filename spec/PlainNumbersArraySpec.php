<?php declare(strict_types=1);

namespace spec\GW\Value;

use BadMethodCallException;
use Closure;
use DivisionByZeroError;
use GW\Value\Arrayable\JustArray;
use GW\Value\Numberable\CompareAsInt;
use GW\Value\Numberable\JustFloat;
use GW\Value\Numberable\JustInteger;
use GW\Value\Numberable\JustNumber;
use GW\Value\Numberable\Zero;
use GW\Value\NumberValue;
use GW\Value\PlainArray;
use GW\Value\PlainNumber;
use GW\Value\PlainNumbersArray;
use GW\Value\Sorts;
use GW\Value\Wrap;
use LogicException;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use function range;

final class PlainNumbersArraySpec extends ObjectBehavior
{
    function it_calculates_sum_of_integers()
    {
        $this->beConstructedThrough('just', [1, 2, 3, 4, 5, 6, 7]);

        $this->sum()->toNumber()->shouldBe(28);
    }

    function it_calculates_sum_of_floats()
    {
        $this->beConstructedThrough('just', [.5, 1.0, 1.5, 2.0, 2.5]);

        $this->sum()->toNumber()->shouldBeApproximately(7.5, 1.0e-9);
    }

    function it_calculates_sum_of_mixed_numerics()
    {
        $this->beConstructedThrough('just', [.5, 1, '1.5', '2', new JustFloat(2.5), PlainNumber::from(2)]);

        $this->sum()->toNumber()->shouldBeApproximately(9.5, 1.0e-9);
    }

    function it_fails_to_create_sum_with_non_numeric()
    {
        $this->beConstructedThrough('just', [.5, 1, 'x']);

        $this->sum()->shouldThrow(LogicException::class)->during('toNumber');
    }

    function it_calculates_average_of_integers()
    {
        $this->beConstructedThrough('just', [1, 2, 3, 4, 5, 6, 7]);

        $this->average()->toNumber()->shouldBe(4);
    }

    function it_calculates_average_of_floats()
    {
        $this->beConstructedThrough('just', [1.1, 1.2, 1.3, 1.4, 1.5, 2.2]);

        $this->average()->toNumber()->shouldBeApproximately(1.45, 1.0e-9);
    }

    function it_calculates_average_of_numerics()
    {
        $this->beConstructedThrough('just', [1.1, '1.2', 1.3, new JustNumber(1.4), PlainNumber::from(1.7), '2']);

        $this->average()->toNumber()->shouldBeApproximately(1.45, 1.0e-9);
    }

    function it_cannot_calculate_average_from_empty_set()
    {
        $this->beConstructedThrough('just', []);

        $this->average()->shouldThrow(DivisionByZeroError::class)->during('toNumber');
    }

    function it_returns_min_of_integers()
    {
        $this->beConstructedThrough('just', [6, 2, 1, 7, -2, 3, 4, 5]);

        $this->min()->toNumber()->shouldBe(-2);
    }

    function it_returns_min_of_floats()
    {
        $this->beConstructedThrough('just', [.6, .2, .1, .7, -.2, .3, .4, .5]);

        $this->min()->toNumber()->shouldBe(-.2);
    }

    function it_returns_min_of_numerics()
    {
        $this->beConstructedThrough('just', [2, .1, '-0.2', new Zero(), '11']);

        $this->min()->toNumber()->shouldBe(-.2);
    }

    function it_returns_max_of_integers()
    {
        $this->beConstructedThrough('just', [6, 2, 1, 7, -2, 3, 4, 5]);

        $this->max()->toNumber()->shouldBe(7);
    }

    function it_returns_max_of_floats()
    {
        $this->beConstructedThrough('just', [.6, .2, .1, .7, -.2, .3, .4, .5]);

        $this->max()->toNumber()->shouldBe(.7);
    }

    function it_returns_max_of_numerics()
    {
        $this->beConstructedThrough('just', ['0.1', '7', -.2, new Zero()]);

        $this->max()->toNumber()->shouldBe(7);
    }

    function it_cannot_calculate_min_nor_max_from_empty_set()
    {
        $this->beConstructedThrough('just', []);

        $this->min()->shouldThrow(LogicException::class)->during('toNumber');
        $this->max()->shouldThrow(LogicException::class)->during('toNumber');
    }

    function it_filters_numbers()
    {
        $this->beConstructedThrough('just', [1, 2, 3, 4, 5, 6, 7]);

        $even = $this->filter(fn(NumberValue $value): bool => $value->toNumber() % 2 === 0);
        $even->shouldBeAnInstanceOf(PlainNumbersArray::class);
        $even->toNativeNumbers()->shouldBe([2, 4, 6]);
    }

    function it_filters_zeros_as_empty_elements()
    {
        $this->beConstructedThrough('just', [1, 2, 3, 0, 4, 5, .0]);

        $notEmpty = $this->filterEmpty();
        $notEmpty->shouldBeAnInstanceOf(PlainNumbersArray::class);
        $notEmpty->toNativeNumbers()->shouldBe([1, 2, 3, 4, 5]);

        $this->notEmpty()->toNativeNumbers()->shouldBe([1, 2, 3, 4, 5]);
    }

    function it_maps_to_ArrayValue()
    {
        $this->beConstructedThrough('just', [1, 2, 3, 4, 5, 6, 7]);

        $mapped = $this->map(fn(NumberValue $number): string => "#{$number->toNumber()}");
        $mapped->beAnInstanceOf(PlainArray::class);
        $mapped->toArray()->shouldBe(['#1', '#2', '#3', '#4', '#5', '#6', '#7']);
    }

    function it_maps_flat_to_ArrayValue()
    {
        $this->beConstructedThrough('just', [1, 2, 3, 4, 5, 6, 7]);

        $mapped = $this->flatMap(
            fn(NumberValue $number): array => ["#{$number->toNumber()}.1", "#{$number->toNumber()}.2"]
        );
        $mapped->beAnInstanceOf(PlainArray::class);
        $mapped->toArray()->shouldBe(
            [
                '#1.1',
                '#1.2',
                '#2.1',
                '#2.2',
                '#3.1',
                '#3.2',
                '#4.1',
                '#4.2',
                '#5.1',
                '#5.2',
                '#6.1',
                '#6.2',
                '#7.1',
                '#7.2',
            ]
        );
    }

    function it_groups_numbers_returning_association_of_numbers_array()
    {
        $this->beConstructedThrough('just', [1, 2, 3, 4, 5]);

        $grouped = $this->groupBy(fn(NumberValue $value) => $value->modulo(new JustInteger(2))->toNumber());

        $even = $grouped->get(0);
        $even->shouldBeAnInstanceOf(PlainNumbersArray::class);
        $even->toNativeNumbers()->shouldBe([2, 4]);

        $odd = $grouped->get(1);
        $odd->toNativeNumbers()->shouldBe([1, 3, 5]);
    }

    function it_chunks_number_values()
    {
        $this->beConstructedThrough('just', [1, 2, 3, 4, 5]);

        $chunked = $this->chunk(2);
        $chunked->shouldBeAnInstanceOf(PlainArray::class);
        $chunked[0]->shouldBeArray();
        $chunked[0]->shouldHaveCount(2);
        $chunked[0][0]->toNumber()->shouldBe(1);
        $chunked[0][1]->toNumber()->shouldBe(2);

        $chunked[1]->shouldBeArray();
        $chunked[1]->shouldHaveCount(2);
        $chunked[1][0]->toNumber()->shouldBe(3);
        $chunked[1][1]->toNumber()->shouldBe(4);

        $chunked[2]->shouldBeArray();
        $chunked[2]->shouldHaveCount(1);
        $chunked[2][0]->toNumber()->shouldBe(5);
    }

    function it_sorts_numbers()
    {
        $this->beConstructedThrough('just', [6, 2, 1, 7, -2, 3, 4, 5]);

        $asc = $this->sort(Sorts::asc());
        $asc->beAnInstanceOf(PlainNumbersArray::class);
        $asc->toNativeNumbers()->shouldBe([-2, 1, 2, 3, 4, 5, 6, 7]);

        $this->sort(Sorts::desc())->toNativeNumbers()->shouldBe([7, 6, 5, 4, 3, 2, 1, -2]);
    }

    function it_reverses_numbers()
    {
        $this->beConstructedThrough('just', [1, 2, 3, 4, 5]);

        $this->reverse()->toNativeNumbers()->shouldBe([5, 4, 3, 2, 1]);
    }

    function it_invokes_callback_for_each_number(CallableMock $callback)
    {
        $this->beConstructedThrough('just', [1, 2, 3, 4, 5]);

        $expect = static fn(int $expected) => Argument::that(fn(NumberValue $n): bool => $n->toNumber() === $expected);
        $callback->__invoke($expect(1))->shouldBeCalled();
        $callback->__invoke($expect(2))->shouldBeCalled();
        $callback->__invoke($expect(3))->shouldBeCalled();
        $callback->__invoke($expect(4))->shouldBeCalled();
        $callback->__invoke($expect(5))->shouldBeCalled();

        $this->each($callback->getWrappedObject());
    }

    function it_joins_numbers_array_value()
    {
        $this->beConstructedThrough('just', [1, 2, 3, 4, 5]);

        $this->join(PlainNumbersArray::just(6, 7, 8))
            ->toNativeNumbers()
            ->shouldBe([1, 2, 3, 4, 5, 6, 7, 8]);

        $this->join(Wrap::array([PlainNumber::from(6), PlainNumber::from(7)]))
            ->toNativeNumbers()
            ->shouldBe([1, 2, 3, 4, 5, 6, 7]);
    }

    function it_slices_numbers_array()
    {
        $this->beConstructedThrough('just', [1, 2, 3, 4, 5]);

        $this->slice(0, 2)->toNativeNumbers()->shouldBe([1, 2]);
        $this->slice(1, 2)->toNativeNumbers()->shouldBe([2, 3]);
        $this->slice(-2, 2)->toNativeNumbers()->shouldBe([4, 5]);
    }

    function it_splices_numbers_array()
    {
        $this->beConstructedThrough('just', [1, 2, 3, 4, 5, 6, 7]);

        $this->splice(0, 4)->toNativeNumbers()->shouldBe([5, 6, 7]);
        $this->splice(2, 3)->toNativeNumbers()->shouldBe([1, 2, 6, 7]);
        $this->splice(-4, 3)->toNativeNumbers()->shouldBe([1, 2, 3, 7]);
        $this->splice(-4, 3, PlainNumbersArray::just(11, 12))
            ->toNativeNumbers()
            ->shouldBe([1, 2, 3, 11, 12, 7]);
    }

    function it_resolves_unique_numbers()
    {
        $this->beConstructedThrough('just', [1, 2, 2, 3, 3.0, 4, 4, 4, 5]);

        $this->unique()->toNativeNumbers()->shouldBe([1, 2, 3, 3.0, 4, 5]);
    }

    function it_resolves_unique_numbers_with_comparator()
    {
        $this->beConstructedThrough('just', [1, 2, 2, 3, 3.0, 4, 4, 4.3, 5, 5.9]);

        $this->unique(CompareAsInt::asc())
            ->toNativeNumbers()
            ->shouldBe([1, 2, 3, 4, 5]);
    }

    function it_resolves_diff()
    {
        $this->beConstructedThrough('just', [1, 2, 2, 3, 3.0, 4, 4, 4, 5]);

        $this->diff(PlainNumbersArray::just(2, 3, 4))->toNativeNumbers()->shouldBe([1, 3.0, 5]);
    }

    function it_resolves_diff_by_comparator()
    {
        $this->beConstructedThrough('just', [1, 2, 2, 3, 3.0, 4, 4, 4.6, 5, 5.9]);

        $this->diff(PlainNumbersArray::just(2, 3, 4), CompareAsInt::desc())->toNativeNumbers()->shouldBe([1, 5, 5.9]);
    }

    function it_resolves_intersect()
    {
        $this->beConstructedThrough('just', [1, 2, 2, 3, 3.0, 4, 4, 4, 5]);

        $this->intersect(PlainNumbersArray::just(2, 3, 4))->toNativeNumbers()->shouldBe([2, 2, 3, 4, 4, 4]);
    }

    function it_resolves_intersect_by_comparator()
    {
        $this->beConstructedThrough('just', [1, 2, 2, 3, 3.0, 4, 4, 4.6, 5, 5.9]);

        $this->intersect(PlainNumbersArray::just(2, 3, 4), CompareAsInt::asc())
            ->toNativeNumbers()
            ->shouldBe([2, 2, 3, 3.0, 4, 4, 4.6]);
    }

    function it_shuffles_numbers()
    {
        $numbers = range(1, 1000);
        $this->beConstructedThrough('just', [...$numbers]);

        $shuffled = $this->shuffle();
        $shuffled->beAnInstanceOf(PlainNumbersArray::class);
        $shuffled->toNativeNumbers()->shouldNotBe($numbers);
        $shuffled->diff($this)->toNativeNumbers()->shouldBe([]);
    }

    function it_unshift_value()
    {
        $this->beConstructedThrough('just', [1, 2, 3, 4, 5]);

        $new = $this->unshift(PlainNumber::from(6));
        $new->toNativeNumbers()->shouldBe([6, 1, 2, 3, 4, 5]);
    }

    function it_shifts_value()
    {
        $this->beConstructedThrough('just', [1, 2, 3, 4, 5]);

        $new = $this->getWrappedObject()->shift($value);

        if (!$value instanceof NumberValue || $value->toNumber() !== 1) {
            throw new FailureException('Expected to shift 1');
        }

        if ($new->toNativeNumbers() !== [2, 3, 4, 5]) {
            throw new FailureException('Expected array 2, 3, 4, 5');
        }
    }

    function it_pushes_value()
    {
        $this->beConstructedThrough('just', [1, 2, 3, 4, 5]);

        $new = $this->push(PlainNumber::from(6));
        $new->toNativeNumbers()->shouldBe([1, 2, 3, 4, 5, 6]);
    }

    function it_pops_value()
    {
        $this->beConstructedThrough('just', [1, 2, 3, 4, 5]);

        $new = $this->getWrappedObject()->pop($value);

        if (!$value instanceof NumberValue || $value->toNumber() !== 5) {
            throw new FailureException('Expected to pop 5');
        }

        if ($new->toNativeNumbers() !== [1, 2, 3, 4]) {
            throw new FailureException('Expected array 1, 2, 3, 4');
        }
    }

    function it_reduces_numbers()
    {
        $this->beConstructedThrough('just', [1, 2, 3, 4, 5]);

        $this->reduce(fn(int $sum, NumberValue $number) => $sum + $number->toNumber(), 0)
            ->shouldBe(15);
    }

    function it_reduces_to_number_value()
    {
        $this->beConstructedThrough('just', [1, 2, 3, 4, 5]);

        $this
            ->reduceNumber(
                fn(NumberValue $sum, NumberValue $number): NumberValue => $sum->add($number),
                PlainNumber::from(0)
            )
            ->toNumber()
            ->shouldBe(15);
    }

    function it_returns_first()
    {
        $this->beConstructedThrough('just', [1, 2, 3, 4, 5]);

        $this->first()->toNumber()->shouldBe(1);
    }

    function it_returns_last()
    {
        $this->beConstructedThrough('just', [1, 2, 3, 4, 5]);

        $this->last()->toNumber()->shouldBe(5);
    }

    function it_returns_null_as_first_and_last_when_empty()
    {
        $this->beConstructedThrough('just', []);

        $this->first()->shouldBeNull();
        $this->last()->shouldBeNull();
    }

    function it_finds_first()
    {
        $this->beConstructedThrough('just', [1, 2, 3, 4, 5]);

        $this->find($this->isEven())->toNumber()->shouldBe(2);
    }

    function it_finds_last()
    {
        $this->beConstructedThrough('just', [1, 2, 3, 4, 5]);

        $this->findLast($this->isEven())->toNumber()->shouldBe(4);
    }

    function it_returns_null_when_not_found()
    {
        $this->beConstructedThrough('just', [1, 2, 3, 4, 5]);

        $this->find($this->isGreater(5))->shouldBeNull();
        $this->findLast($this->isGreater(5))->shouldBeNull();
    }

    function it_resolves_any_and_every_condition()
    {
        $this->beConstructedThrough('just', [1, 2, 3, 4, 5]);

        $this->any($this->isEven())->shouldBe(true);
        $this->any($this->isGreater(4))->shouldBe(true);
        $this->any($this->isGreater(5))->shouldBe(false);


        $this->every($this->isEven())->shouldBe(false);
        $this->every($this->isGreater(1))->shouldBe(false);
        $this->every($this->isGreater(0))->shouldBe(true);
    }

    function it_resolves_that_array_has_element_strict()
    {
        $two = PlainNumber::from(2);
        $numbers = new JustArray([PlainNumber::from(1), $two]);
        $this->beConstructedThrough('fromArrayable', [$numbers]);

        $this->hasElement($two)->shouldBe(true);
        $this->hasElement(PlainNumber::from(2))->shouldBe(false);
    }

    function it_is_countable()
    {
        $this->beConstructedThrough('just', [1, 2, 3, 4, 5]);

        $this->count()->shouldBe(5);
    }

    function it_is_arrayable()
    {
        $one = PlainNumber::from(1);
        $two = PlainNumber::from(2);
        $numbers = new JustArray([$one, $two]);
        $this->beConstructedThrough('fromArrayable', [$numbers]);

        $this->toArray()->shouldBe([$one, $two]);
    }

    function it_is_iterable()
    {
        $one = PlainNumber::from(1);
        $two = PlainNumber::from(2);
        $numbers = new JustArray([$one, $two]);
        $this->beConstructedThrough('fromArrayable', [$numbers]);

        $this->shouldIterateLike([$one, $two]);
    }

    function it_has_immutable_array_access()
    {
        $one = PlainNumber::from(1);
        $two = PlainNumber::from(2);
        $numbers = new JustArray([$one, $two]);
        $this->beConstructedThrough('fromArrayable', [$numbers]);

        $this[0]->shouldBe($one);
        $this[1]->shouldBe($two);
        $this->offsetExists(0)->shouldBe(true);
        $this->offsetExists(2)->shouldBe(false);

        $this->shouldThrow(BadMethodCallException::class)->during('offsetSet', [1, PlainNumber::from(3)]);
        $this->shouldThrow(BadMethodCallException::class)->during('offsetUnset', [1]);
    }

    function it_implodes_to_string_value()
    {
        $this->beConstructedThrough('just', [1, 2.5, 3.6, 4, 5]);

        $this->implode(', ')->toString()->shouldBe('1, 2.5, 3.6, 4, 5');
    }

    function it_is_not_empty_when_has_number()
    {
        $this->beConstructedThrough('just', [0]);

        $this->isEmpty()->shouldBe(false);
    }

    function it_is_empty_when_no_numbers()
    {
        $this->beConstructedThrough('just', []);

        $this->isEmpty()->shouldBe(true);
    }

    function it_can_be_casted_to_association()
    {
        $one = PlainNumber::from(1);
        $two = PlainNumber::from(2);
        $three = PlainNumber::from(3);
        $four = PlainNumber::from(4);
        $numbers = new JustArray([$one, $two, $three, $four]);
        $this->beConstructedThrough('fromArrayable', [$numbers]);

        $this->toAssocValue()->filter($this->isEven())->toAssocArray()->shouldBe([1 => $two, 3 => $four]);
    }

    function it_can_be_casted_to_strings_array()
    {
        $this->beConstructedThrough('just', [1, 2.5, 3.6, 4, 5]);

        $this->toStringsArray()->toNativeStrings()->shouldBe(['1', '2.5', '3.6', '4', '5']);
    }

    private function isEven(): Closure
    {
        return static fn(NumberValue $number) => $number->toNumber() % 2 === 0;
    }

    private function isGreater(int $than): Closure
    {
        return static fn(NumberValue $number) => $number->toNumber() > $than;
    }
}
