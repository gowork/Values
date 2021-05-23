<?php

namespace spec\GW\Value;

use GW\Value\ArrayValue;
use GW\Value\PlainArray;
use GW\Value\PlainStringsArray;
use GW\Value\Sorts;
use GW\Value\StringsArray;
use GW\Value\StringValue;
use GW\Value\Wrap;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Collaborator;
use Prophecy\Argument;

final class PlainStringsArraySpec extends ObjectBehavior
{
    function it_is_initializable_with_primitive_string()
    {
        $this->beConstructedWithStrings('string');

        $this->shouldHaveType(PlainStringsArray::class);

        $this->count()->shouldReturn(1);
        $this->toString()->shouldReturn('string');
    }

    function it_is_initializable_with_string_value()
    {
        $this->beConstructedWith(Wrap::array([Wrap::string('string')]));

        $this->shouldHaveType(PlainStringsArray::class);

        $this->count()->shouldReturn(1);
        $this->toString()->shouldReturn('string');
    }

    function it_joins_with_other_StringsArray()
    {
        $this->beConstructedWithStrings('string 1', 'string 2');

        $join = $this->join(PlainStringsArray::fromArray(['string 3', 'string 4']));
        $join->shouldNotBe($this);
        $join->toNativeStrings()->shouldBeLike(['string 1', 'string 2', 'string 3', 'string 4']);
    }

    function it_joins_with_regular_ArrayValue_with_strings()
    {
        $this->beConstructedWithStrings('string 1', 'string 2');

        $this->join(Wrap::array(['string 3', 'string 4']))
            ->toNativeStrings()->shouldBeLike(['string 1', 'string 2', 'string 3', 'string 4']);
    }

    function it_can_be_sliced()
    {
        $strings = ['string 1', 'string 2', 'string 3', 'string 4'];
        $this->beConstructedWithStrings(...$strings);

        $this->slice(0, 1)->shouldNotBe($this);
        $this->slice(0, 1)->toNativeStrings()->shouldBeLike(['string 1']);
        $this->slice(3, 1)->toNativeStrings()->shouldBeLike(['string 4']);
        $this->slice(1, 2)->toNativeStrings()->shouldBeLike(['string 2', 'string 3']);
        $this->slice(0, 4)->toNativeStrings()->shouldBeLike($strings);
        $this->slice(0, 500)->toNativeStrings()->shouldBeLike($strings);
    }

    function it_can_be_spliced()
    {
        $this->beConstructedWithStrings('string 1', 'string 2', 'string 3', 'string 4');

        $this->splice(1, 2)->toNativeStrings()->shouldBeLike(['string 1', 'string 4']);

        $this->splice(1, 2, Wrap::array(['string x', 'string y']))
            ->toNativeStrings()->shouldBeLike(['string 1', 'string x', 'string y', 'string 4']);

        $this->splice(0, 3)->toNativeStrings()->shouldBeLike(['string 4']);

        $this->splice(-1, 1)->toNativeStrings()->shouldBeLike(['string 1', 'string 2', 'string 3']);

        $this->splice(0, 100)->toNativeStrings()->shouldBeLike([]);

        $this->splice(0, 0, PlainStringsArray::fromArray(['string 0']))
            ->toNativeStrings()->shouldBeLike(['string 0', 'string 1', 'string 2', 'string 3', 'string 4']);
    }

    function it_returns_diff()
    {
        $strings = ['string 1', 'string 2', 'string 3', 'string 4'];
        $this->beConstructedWithStrings(...$strings);

        $diff = $this->diff(PlainStringsArray::fromArray(['string 2', 'string 3']));
        $diff->shouldNotBe($this);
        $diff->toNativeStrings()->shouldBeLike(['string 1', 'string 4']);

        $this->diff(PlainStringsArray::fromArray(['string 1', 'string 2', 'string 3', 'string 4']))
            ->toNativeStrings()->shouldBeLike([]);

        $this->diff(PlainStringsArray::fromArray([]))->toNativeStrings()->shouldBeLike($strings);
    }

    function it_returns_intersection()
    {
        $strings = ['string 1', 'string 2', 'string 3', 'string 4'];
        $this->beConstructedWithStrings(...$strings);

        $intersect = $this->intersect(PlainStringsArray::fromArray(['string 2', 'string 3']));
        $intersect->shouldNotBe($this);
        $intersect->toNativeStrings()->shouldBeLike(['string 2', 'string 3']);

        $this->intersect(PlainStringsArray::fromArray(['string 1', 'string 2', 'string 3', 'string 4']))
            ->toNativeStrings()
            ->shouldBeLike($strings);

        $this->intersect(PlainStringsArray::fromArray([]))->toNativeStrings()->shouldBeLike([]);
    }

    function it_reduces_array_to_single_value()
    {
        $this->beConstructedWithStrings('c', 'o', 'o', 'l');

        $reducer = function (StringValue $reduced, StringValue $value) {
            return $reduced->postfix($value);
        };

        $this->reduce($reducer, Wrap::string('so '))->shouldBeLike(Wrap::string('so cool'));
    }

    function it_maps_values_to_another_StringsArray()
    {
        $this->beConstructedWithStrings('aaa', 'bbb', 'ccc', 'ddd');

        $mapper = function (StringValue $value): StringValue {
            return $value->upper();
        };

        $this->map($mapper)->toNativeStrings()->shouldBeLike(['AAA', 'BBB', 'CCC', 'DDD']);
    }

    function it_maps_values_to_another_StringsArray_with_mapper_returning_string()
    {
        $this->beConstructedWithStrings('aaa', 'bbb', 'ccc', 'ddd');

        $mapper = function (StringValue $value): string {
            return $value->upper()->toString();
        };

        $this->map($mapper)->toNativeStrings()->shouldBeLike(['AAA', 'BBB', 'CCC', 'DDD']);
    }

    function it_flat_maps_string_values()
    {
        $this->beConstructedWithStrings('blue ball', 'red balloon', 'green grass');

        $mapped = $this->flatMap(
            function (StringValue $words): StringsArray {
                return $words->explode(' ');
            }
        );

        $mapped->shouldNotBe($this);
        $mapped->toNativeStrings()->shouldBeLike(['blue', 'ball', 'red', 'balloon', 'green', 'grass']);
    }

    function it_can_group_items()
    {
        $this->beConstructedWithStrings('red', 'green', 'blue', 'black');

        $this
            ->groupBy(
                function (StringValue $value): string {
                    return $value->substring(0, 1)->toString();
                }
            )
            ->toAssocArray()
            ->shouldBeLike(
                [
                    'r' => PlainStringsArray::fromArray(['red']),
                    'g' => PlainStringsArray::fromArray(['green']),
                    'b' => PlainStringsArray::fromArray(['blue', 'black']),
                ]
            );
    }

    function it_returns_first_and_last()
    {
        $this->beConstructedWithStrings('first', 'second', 'third');

        $this->first()->shouldBeLike(Wrap::string('first'));
        $this->last()->shouldBeLike(Wrap::string('third'));
    }

    function it_returns_null_from_first_and_last_method_when_collection_is_empty()
    {
        $this->beConstructedWithStrings();

        $this->first()->shouldReturn(null);
        $this->last()->shouldReturn(null);
    }

    function it_calls_a_function_on_each_element(CallableMock $callable)
    {
        $this->beConstructedWithStrings('first', 'second', 'third');

        $this->each($callable);

        $callable->__invoke(Wrap::string('first'))->shouldHaveBeenCalled();
        $callable->__invoke(Wrap::string('second'))->shouldHaveBeenCalled();
        $callable->__invoke(Wrap::string('third'))->shouldHaveBeenCalled();
    }

    function it_returns_StringsArray_with_unique_values()
    {
        $this->beConstructedWithStrings('first', 'first', 'second', 'third');

        $unique = $this->unique();
        $unique->shouldNotBe($this);
        $unique->toNativeStrings()->shouldBeLike(['first', 'second', 'third']);
    }

    function it_returns_StringsArray_with_unique_values_using_comparator()
    {
        $this->beConstructedWithStrings('first', 'FIRST', 'second');

        $this->unique()->toNativeStrings()->shouldBeLike(['first', 'FIRST', 'second']);

        $lowerComparator = function (StringValue $valueA, StringValue $valueB): int {
            return $valueA->lower() <=> $valueB->lower();
        };

        $this->unique($lowerComparator)->toNativeStrings()->shouldBeLike(['first', 'second']);
    }

    function it_returns_regular_array()
    {
        $this->beConstructedWithStrings('first', 'second', 'third');

        $this->toArray()
            ->shouldBeLike([Wrap::string('first'), Wrap::string('second'), Wrap::string('third')]);
    }

    function it_filters_empty_strings()
    {
        $this->beConstructedWithStrings('first', '', ' ', 'second', '0', 'false');

        $this->count()->shouldReturn(6);

        $notEmpty = $this->filterEmpty();

        $notEmpty->shouldNotBe($this);
        $notEmpty->toNativeStrings()->shouldBeLike(['first', ' ', 'second', '0', 'false']);
    }

    function it_sorts_ascending()
    {
        $this->beConstructedWithStrings('beta', 'alpha', 'zeta', 'omega');

        $sorted = $this->sort(Sorts::asc());
        $sorted->shouldNotBeLike($this);
        $sorted->toNativeStrings()->shouldBeLike(['alpha', 'beta', 'omega', 'zeta']);
    }

    function it_sorts_descending()
    {
        $this->beConstructedWithStrings('beta', 'alpha', 'zeta', 'omega');

        $sorted = $this->sort(Sorts::desc());
        $sorted->shouldNotBeLike($this);
        $sorted->toNativeStrings()->shouldBeLike(['zeta', 'omega', 'beta', 'alpha']);
    }

    function it_shuffles_string_items(ArrayValue $strings)
    {
        $this->beConstructedWith($strings);

        $this->prepareArrayCollaborator($strings);

        $strings->shuffle()->shouldBeCalled()->willReturn($strings);

        $shuffled = $this->shuffle();
        $shuffled->shouldNotBe($this);
        $shuffled->shouldHaveType(PlainStringsArray::class);
    }

    function it_reverses_containing_string()
    {
        $this->beConstructedWithStrings('Yoda', 'is', 'name', 'My');

        $reversed = $this->reverse();
        $reversed->shouldNotBe($this);
        $reversed->toNativeStrings()->shouldBeLike(['My', 'name', 'is', 'Yoda']);
    }

    function it_can_be_iterated()
    {
        $this->beConstructedWithStrings('first', 'second');

        $this->shouldImplement(\IteratorAggregate::class);
        $this->getIterator()->shouldImplement(\Iterator::class);
    }

    function it_is_like_array()
    {
        $this->beConstructedWithStrings('first', 'second');

        $this->shouldImplement(\ArrayAccess::class);

        $this[0]->shouldBeLike(Wrap::string('first'));
        $this->offsetGet(0)->shouldBeLike(Wrap::string('first'));

        $this->offsetExists(0)->shouldReturn(true);
        $this->offsetExists(2)->shouldReturn(false);
    }

    function it_is_like_array_but_immutable()
    {
        $this->beConstructedWithStrings('first', 'second');

        $this->shouldThrow(\BadMethodCallException::class)->during('offsetSet', [0, Wrap::string('mutant')]);
        $this->shouldThrow(\BadMethodCallException::class)->during('offsetUnset', [0]);
    }

    function it_returns_count_of_contained_strings()
    {
        $this->beConstructedWithStrings('one', 'two', 'three', 'four', 'five');

        $this->count()->shouldReturn(5);
    }

    function it_prepends_string()
    {
        $this->beConstructedWithStrings('one', 'two', 'three');

        $added = $this->unshift('zero');
        $added->shouldNotBe($this);
        $added->toNativeStrings()->shouldBeLike(['zero', 'one', 'two', 'three']);
    }

    function it_prepends_StringValue()
    {
        $this->beConstructedWithStrings('one', 'two', 'three');

        $added = $this->unshift(Wrap::string('zero'));
        $added->shouldNotBe($this);
        $added->toNativeStrings()->shouldBeLike(['zero', 'one', 'two', 'three']);
    }

    function it_shift_item_from_the_beginning_of_strings_array()
    {
        $this->beConstructedWithStrings('one', 'two');
        $clone = PlainStringsArray::fromArray(['one', 'two']);

        $reduced = $this->shift();
        $reduced->shouldBeLike($clone->shift($one));

        if ($one != Wrap::string('one')) {
            throw new FailureException('Shifted value should be assigned to provided variable');
        }

        $reduced->count()->shouldReturn(1);
    }

    function it_appends_string()
    {
        $this->beConstructedWithStrings('one', 'two');

        $appended = $this->push('three');
        $appended->shouldNotBe($this);
        $appended->toNativeStrings()->shouldBeLike(['one', 'two', 'three']);
    }

    function it_appends_StringValue()
    {
        $this->beConstructedWithStrings('one', 'two');

        $appended = $this->push(Wrap::string('three'));
        $appended->shouldNotBe($this);
        $appended->toNativeStrings()->shouldBeLike(['one', 'two', 'three']);
    }

    function it_pops_string_from_the_end_of_array()
    {
        $this->beConstructedWithStrings('one', 'two', 'last');
        $clone = PlainStringsArray::fromArray(['one', 'two', 'last']);

        $popped = $this->pop();
        $popped->shouldBeLike($clone->pop($last));

        if ($last != Wrap::string('last')) {
            throw new FailureException('Popped value should be assigned to provided variable');
        }

        $this->count()->shouldReturn(3);
        $popped->count()->shouldReturn(2);
    }

    function it_strip_tags_from_all_strings()
    {
        $this->beConstructedWithStrings(
            '<h1>This is a header</h1>',
            '<h3>This is a subtitle</h3>',
            "<javascript> alert('This is javascript'); </javascript>"
        );

        $stripped = $this->stripTags();
        $stripped->shouldNotBe($this);
        $stripped->toNativeStrings()->shouldBeLike(
            ['This is a header', 'This is a subtitle', ' alert(\'This is javascript\'); ']
        );
    }

    function it_trims_all_contained_strings()
    {
        $this->beConstructedWithStrings(" \t\n one  ", "  two \n\r ");

        $this->trim()->toNativeStrings()->shouldBeLike(['one', 'two']);
    }

    function it_trims_all_contained_strings_with_custom_characters()
    {
        $this->beConstructedWithStrings(' xxx one xxx ', 'xxxtwoxxx', ' three ');

        $this->trim(' x')->toNativeStrings()->shouldBeLike(['one', 'two', 'three']);
    }

    function it_trims_right_all_contained_strings()
    {
        $this->beConstructedWithStrings(" \t\n one  ", "  two \n\r ");

        $this->trimRight()->toNativeStrings()->shouldBeLike([" \t\n one", "  two"]);
    }

    function it_trims_right_all_contained_strings_with_custom_characters()
    {
        $this->beConstructedWithStrings(' xxx one xxx ', 'xxxtwoxxx', ' three ');

        $this->trimRight(' x')->toNativeStrings()->shouldBeLike([' xxx one', 'xxxtwo', ' three']);
    }

    function it_trims_left_all_contained_strings()
    {
        $this->beConstructedWithStrings(" \t\n one  ", "  two \n\r ");

        $this->trimLeft()->toNativeStrings()->shouldBeLike(["one  ", "two \n\r "]);
    }

    function it_trims_left_all_contained_strings_with_custom_characters()
    {
        $this->beConstructedWithStrings(' xxx one xxx ', 'xxxtwoxxx', ' three ');

        $this->trimLeft(' x')->toNativeStrings()->shouldBeLike(['one xxx ', 'twoxxx', 'three ']);
    }

    function it_transforms_strings_to_lower_case()
    {
        $this->beConstructedWithStrings('Will', 'Will', 'SMITH', 'smith?');

        $lower = $this->lower();
        $lower->shouldNotBe($this);
        $lower->toNativeStrings()->shouldBeLike(['will', 'will', 'smith', 'smith?']);
    }

    function it_transforms_national_characters_to_lower_case()
    {
        $this->beConstructedWithStrings('zaŻÓŁĆ', 'gĘślĄ', 'jaŹŃ');

        $lower = $this->lower();
        $lower->shouldNotBe($this);
        $lower->toNativeStrings()->shouldBeLike(['zażółć', 'gęślą', 'jaźń']);
    }

    function it_transforms_strings_to_upper_case()
    {
        $this->beConstructedWithStrings('will', 'will', 'smith', 'smith?');

        $upper = $this->upper();
        $upper->shouldNotBe($this);
        $upper->toNativeStrings()->shouldBeLike(['WILL', 'WILL', 'SMITH', 'SMITH?']);
    }

    function it_transforms_national_characters_to_upper_case()
    {
        $this->beConstructedWithStrings('zażółć', 'gęślą', 'jaźń');

        $upper = $this->upper();
        $upper->shouldNotBe($this);
        $upper->toNativeStrings()->shouldBeLike(['ZAŻÓŁĆ', 'GĘŚLĄ', 'JAŹŃ']);
    }

    function it_transforms_first_letter_to_lower_case_in_all_strings()
    {
        $this->beConstructedWithStrings('ŻABA', 'MEANS', 'FROG');

        $lower = $this->lowerFirst();
        $lower->shouldNotBe($this);
        $lower->toNativeStrings()->shouldBeLike(['żABA', 'mEANS', 'fROG']);
    }

    function it_transforms_first_letter_to_upper_case_in_all_strings()
    {
        $this->beConstructedWithStrings('żaba', 'means', 'frog');

        $lower = $this->upperFirst();
        $lower->shouldNotBe($this);
        $lower->toNativeStrings()->shouldBeLike(['Żaba', 'Means', 'Frog']);
    }

    function it_can_be_converted_to_ArrayValue_containing_StringValue()
    {
        $this->beConstructedWithStrings('one', 'two', 'three');

        $this->toArrayValue()
            ->toArray()
            ->shouldBeLike([Wrap::string('one'), Wrap::string('two'), Wrap::string('three')]);
    }

    function it_can_be_converted_to_AssocValue_containing_StringValue()
    {
        $this->beConstructedWithStrings('one', 'two', 'three');

        $this->toAssocValue()
            ->toAssocArray()
            ->shouldBeLike([0 => Wrap::string('one'), 1 => Wrap::string('two'), 2 => Wrap::string('three')]);
    }

    function it_implements_toStringsArray_from_ArrayValue_returning_self()
    {
        $this->beConstructedWithStrings('one', 'two', 'three');

        $this->toStringsArray()->shouldReturn($this);
    }

    function it_can_tell_is_has_a_string()
    {
        $this->beConstructedWithStrings('one', 'two', 'three');

        $this->hasElement('one')->shouldReturn(true);
        $this->hasElement('four')->shouldReturn(false);
    }

    function it_can_tell_is_has_a_wrapped_string()
    {
        $this->beConstructedWithStrings('one', 'two', 'three');

        $this->hasElement(Wrap::string('one'))->shouldReturn(true);
        $this->hasElement(Wrap::string('five'))->shouldReturn(false);
    }

    function it_transforms_all_strings_with_custom_callback()
    {
        $this->beConstructedWithStrings('one', 'two', 'three');

        $transformed = $this->transform('md5');
        $transformed->shouldNotBe($this);
        $transformed->toNativeStrings()->shouldBeLike([md5('one'), md5('two'), md5('three')]);
    }

    function it_returns_PlainArray_containing_regex_matches()
    {
        $this->beConstructedWithStrings('Lorem', 'ipsum', 'dolor', 'sit', 'amet');

        $matches = $this->matchAllPatterns('/(lorem|dolor)/ui');
        $matches->shouldNotBe($this);
        $matches->shouldBeLike(new PlainArray([['Lorem', 'Lorem'], ['dolor', 'dolor']]));
    }

    function it_can_be_converted_to_NumbersArray()
    {
        $this->beConstructedWithStrings('1', '2.1', '3.0');

        $this->toNumbersArray()
            ->toNativeNumbers()
            ->shouldBeLike([1, 2.1, 3.0]);
    }

    private function beConstructedWithStrings(string ...$strings): void
    {
        $this->beConstructedWith(Wrap::array($strings));
    }

    /**
     * @param ArrayValue|Collaborator $strings
     */
    private function prepareArrayCollaborator($strings): void
    {
        $strings->map(Argument::type('callable'))->willReturn($strings);
        $strings->each(Argument::type('callable'))->willReturn($strings);
    }
}
