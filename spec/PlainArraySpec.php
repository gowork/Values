<?php

namespace spec\GW\Value;

use GW\Value\ArrayValue;
use GW\Value\Filters;
use GW\Value\Numberable\JustInteger;
use GW\Value\NumbersArray;
use GW\Value\PlainArray;
use GW\Value\Sorts;
use GW\Value\StringsArray;
use GW\Value\Wrap;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Subject;

final class PlainArraySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith([]);
        $this->shouldHaveType(PlainArray::class);
    }

    function it_is_countable()
    {
        $this->beConstructedWith(['item 1', 'item 2']);
        $this->count()->shouldReturn(2);
    }

    function it_returns_items()
    {
        $items = ['item 1', 'item 2', 'item 3'];
        $this->beConstructedWith($items);
        $this->toArray()->shouldReturn($items);
    }

    function it_removes_keys_from_associative_array()
    {
        $items = ['item 1' => 'item 1', '2' => 'item 2', 'three' => 'item 3'];
        $this->beConstructedWith($items);
        $this->toArray()->shouldReturn(['item 1', 'item 2', 'item 3']);
    }

    function it_maps_string_items_with_closure()
    {
        $this->beConstructedWith(['item 1', 'item 2', 'item 3']);

        $mapped = $this->map(function (string $item): string {
            return $item . ' mapped';
        });

        $mapped->shouldNotBe($this);
        $mapped->toArray()->shouldBeLike(['item 1 mapped', 'item 2 mapped', 'item 3 mapped']);
    }

    function it_maps_items_with_php_callable()
    {
        $this->beConstructedWith(['100', '50.12', '', true, false]);

        $mapped = $this->map('intval');

        $mapped->shouldNotBe($this);
        $mapped->toArray()->shouldBeLike([100, 50, 0, 1, 0]);
    }

    function it_maps_with_flattening_with_array_transformer()
    {
        $this->shouldFlatMapBandMembersWith(
            function (array $band): array {
                return $band['members'];
            }
        );
    }

    function it_maps_with_flattening_with_iterator_transformer()
    {
        $this->shouldFlatMapBandMembersWith(
            function (array $band): \Iterator {
                return new \ArrayIterator($band['members']);
            }
        );
    }

    function it_can_flat_map_empty_array()
    {
        $this->beConstructedWith([[], []]);

        $transformer = function (): array {
            return [];
        };

        $this->flatMap($transformer)->toArray()->shouldBeLike([]);
    }

    function it_can_flat_map_same_keys()
    {
        $this->beConstructedWith([1]);

        $transformer = function (): iterable {
            yield from [1,2,3];
            yield from [4,5,6];
        };

        $this->flatMap($transformer)->toArray()->shouldBeLike([1,2,3,4,5,6]);
    }

    function it_can_group_elements_by_key_generated_from_value()
    {
        $this->beConstructedWith([1, 2, 3, 4, 5, 10, 100, 101]);

        $transformer = function (int $value): string {
            return ($value % 2) === 0 ? 'even' : 'odd';
        };

        $grouped = $this->groupBy($transformer);
        $even = $grouped->get('even');
        $even->shouldBeAnInstanceOf(ArrayValue::class);
        $even->toArray()->shouldBe([2, 4, 10, 100]);

        $odd = $grouped->get('odd');
        $odd->shouldBeAnInstanceOf(ArrayValue::class);
        $odd->toArray()->shouldBe([1, 3, 5, 101]);
    }

    function it_can_be_grouped_by_integer_key()
    {
        $this->beConstructedWith([1, 2, 3, 4, 5, 6, 7, 8, 9]);

        $transformer = function (int $value): int {
            return $value % 3;
        };

        $grouped = $this->groupBy($transformer);
        $grouped->get('0')->toArray()->shouldReturn([3, 6, 9]);
        $grouped->get('1')->toArray()->shouldReturn([1, 4, 7]);
        $grouped->get('2')->toArray()->shouldReturn([2, 5, 8]);
    }

    function it_can_be_partitioned_by_boolean_value()
    {
        $this->beConstructedWith([1, 2, 3, 4, 5, 6, 7, 8, 9]);

        $isEven = function (int $value): bool {
            return ($value % 2) === 0;
        };

        $grouped = $this->groupBy($isEven);
        $grouped->get(1)->toArray()->shouldReturn([2, 4, 6, 8]);
        $grouped->get(0)->toArray()->shouldReturn([1, 3, 5, 7, 9]);
    }

    function it_allows_to_check_if_any_element_satisfies_filter_condition()
    {
        $this->beConstructedWith([2, 4, 6, 8, 10, 12, 14, 16]);

        $isEven = function (int $value): bool {
            return ($value % 2) === 0;
        };
        $isOdd = Filters::not($isEven);
        $isTwo = Filters::equal(2);
        $isHundred = Filters::equal(100);

        $this->any($isEven)->shouldReturn(true);
        $this->any($isTwo)->shouldReturn(true);

        $this->any($isOdd)->shouldReturn(false);
        $this->any($isHundred)->shouldReturn(false);
    }

    function it_allows_to_check_if_every_element_satisfies_filter_condition()
    {
        $this->beConstructedWith([2, 4, 6, 8, 10, 12, 14, 16]);

        $isEven = function (int $value): bool {
            return ($value % 2) === 0;
        };
        $isOdd = Filters::not($isEven);
        $isTwo = Filters::equal(2);
        $isHundred = Filters::equal(100);

        $this->every($isEven)->shouldReturn(true);

        $this->every($isTwo)->shouldReturn(false);
        $this->every($isOdd)->shouldReturn(false);
        $this->every($isHundred)->shouldReturn(false);
    }

    function it_allows_to_split_array_to_chunks_of_given_size()
    {
        $this->beConstructedWith([1, 2, 3, 4, 5, 6, 7, 8, 9]);

        $this->chunk(3)->toArray()->shouldBeLike([[1, 2, 3], [4, 5, 6], [7, 8, 9]]);
        $this->chunk(5)->toArray()->shouldBeLike([[1, 2, 3, 4, 5], [6, 7, 8, 9]]);
    }

    private function shouldFlatMapBandMembersWith(callable $mapper): void
    {
        $this->beConstructedWith([
            [
                'band' => 'The Beatles',
                'members' => ['John', 'Paul', 'George', 'Ringo'],
            ],
            [
                'band' => 'Rolling Stones',
                'members' => ['Mick', 'Keith', 'Ron', 'Charlie'],
            ],
            [
                'band' => 'Led Zeppelin',
                'members' => ['Robert', 'Jimmy', 'John Paul', 'John'],
            ],
        ]);

        $legends = $this->flatMap($mapper);
        $legends->shouldNotBe($this);
        $legends->toArray()->shouldBeLike(
            [
                'John',
                'Paul',
                'George',
                'Ringo',
                'Mick',
                'Keith',
                'Ron',
                'Charlie',
                'Robert',
                'Jimmy',
                'John Paul',
                'John',
            ]
        );
    }

    function it_filters_items_with_closure()
    {
        $this->beConstructedWith([
            'Because I`m bad',
            'You know I`m bad',
            'And the whole world has to',
            'Answer right now',
            'Just to tell you once again',
            'Who`s bad',
        ]);

        $notBad = $this->filter(function (string $line): bool {
            return strpos($line, 'bad') === false;
        });

        $notBad->shouldNotBe($this);
        $notBad->toArray()->shouldBeLike(
            [
                'And the whole world has to',
                'Answer right now',
                'Just to tell you once again',
            ]
        );
    }

    function it_filters_with_php_callable()
    {
        $this->beConstructedWith(['Hello', new \stdClass(), '1410', 3.14, true]);

        $numbers = $this->filter('is_numeric');

        $numbers->shouldNotBe($this);
        $numbers->toArray()->shouldBeLike(['1410', 3.14]);
    }

    function it_sorts_numbers_ascending()
    {
        $this->beConstructedWith([4, 1, 8, 2, 9, 3, 5, 7, 6]);

        $sorted = $this->sort(Sorts::asc());
        $sorted->toArray()->shouldBeLike([1, 2, 3, 4, 5, 6, 7, 8, 9]);
    }

    function it_sorts_numbers_descending()
    {
        $this->beConstructedWith([4, 1, 8, 2, 9, 3, 5, 7, 6]);

        $sorted = $this->sort(Sorts::desc());
        $sorted->toArray()->shouldBeLike([9, 8, 7, 6, 5, 4, 3, 2, 1]);
    }

    function it_sorts_strings_ascending()
    {
        $this->beConstructedWith(['def', 'efg', 'abc', 'xyz', 'zzz']);

        $sorted = $this->sort(Sorts::asc());
        $sorted->toArray()->shouldBeLike(['abc', 'def', 'efg', 'xyz', 'zzz']);
    }

    function it_sorts_strings_descending()
    {
        $this->beConstructedWith(['def', 'efg', 'abc', 'xyz', 'zzz']);

        $sorted = $this->sort(Sorts::desc());
        $sorted->toArray()->shouldBeLike(['zzz', 'xyz', 'efg', 'def', 'abc']);
    }

    function it_can_call_some_action_on_each_item(CallableMock $callable)
    {
        $this->beConstructedWith(['item 1', 'item 2', 'item 3']);

        $this->each($callable);

        $callable->__invoke('item 1')->shouldHaveBeenCalled();
        $callable->__invoke('item 2')->shouldHaveBeenCalled();
        $callable->__invoke('item 3')->shouldHaveBeenCalled();
    }

    function it_reverses_items_order()
    {
        $this->beConstructedWith(['item 1', 'item 2', 'item 3']);

        $reversed = $this->reverse();

        $reversed->shouldNotBe($this);
        $reversed->toArray()->shouldBeLike(['item 3', 'item 2', 'item 1']);
    }

    function it_joins_with_other_array()
    {
        $this->beConstructedWith(['a 1', 'a 2', 'a 3']);

        $joined = $this->join(new PlainArray(['b 1', 'b 2', 'b 3']));

        $joined->shouldNotBe($this);
        $joined->toArray()->shouldBeLike(['a 1', 'a 2', 'a 3', 'b 1', 'b 2', 'b 3']);
    }

    function it_slices_given_part()
    {
        $this->beConstructedWith(['item 1', 'item 2', 'item 3', 'item 4', 'item 5', 'item 6']);

        $this->slice(0, 1)->shouldNotBe($this);
        $this->slice(0, 1)->toArray()->shouldBeLike(['item 1']);
        $this->slice(1, 4)->toArray()->shouldBeLike(['item 2', 'item 3', 'item 4', 'item 5']);
        $this->slice(5, 1)->toArray()->shouldBeLike(['item 6']);
    }

    function it_allows_to_remove_slice_from_array_with_splice()
    {
        $this->beConstructedWith(['item 1', 'item 2', 'item 3', 'item 4', 'item 5', 'item 6']);

        $this->splice(0, 1)->shouldNotBe($this);
        $this->splice(0, 0)->toArray()->shouldBeLike($this->toArray());
        $this->splice(0, 1)->toArray()->shouldBeLike(['item 2', 'item 3', 'item 4', 'item 5', 'item 6']);
        $this->splice(1, 4)->toArray()->shouldBeLike(['item 1', 'item 6']);
        $this->splice(5, 1)->toArray()->shouldBeLike(['item 1', 'item 2', 'item 3', 'item 4', 'item 5']);
        $this->splice(-1, 1)->toArray()->shouldBeLike(['item 1', 'item 2', 'item 3', 'item 4', 'item 5']);
    }

    function it_allows_to_replace_slice_of_array_with_splice()
    {
        $this->beConstructedWith(['item 1', 'item 2', 'item 3', 'item 4', 'item 5', 'item 6']);

        $this->splice(0, 1, new PlainArray(['X', 'Y']))
            ->toArray()->shouldBeLike(['X', 'Y', 'item 2', 'item 3', 'item 4', 'item 5', 'item 6']);

        $this->splice(1, 4, new PlainArray(['X', 'Y']))
            ->toArray()->shouldBeLike(['item 1', 'X', 'Y','item 6']);

        $this->splice(5, 1, new PlainArray(['X', 'Y']))
            ->toArray()->shouldBeLike(['item 1', 'item 2', 'item 3', 'item 4', 'item 5', 'X', 'Y']);

        $this->splice(-1, 1, new PlainArray(['X', 'Y']))
            ->toArray()->shouldBeLike(['item 1', 'item 2', 'item 3', 'item 4', 'item 5', 'X', 'Y']);
    }

    function it_can_return_clone_with_unique_values_comparing_them_as_strings()
    {
        $this->beConstructedWith(['item 1', 'item 2', 'item 2', 'item 3', 'item 4', 'item 4', 'item 5']);

        $unique = $this->unique();

        $unique->shouldNotBe($this);
        $unique->toArray()->shouldBeLike(['item 1', 'item 2', 'item 3', 'item 4', 'item 5']);
    }

    function it_can_return_clone_with_unique_values_comparing_them_using_comparator()
    {
        $this->beConstructedWith([
            new DummyEntity(1, 'Joe'),
            new DummyEntity(1, 'Joey'),
            new DummyEntity(2, 'William'),
            new DummyEntity(2, 'Will'),
            new DummyEntity(3, 'Jack'),
            new DummyEntity(4, 'Averell'),
            new DummyEntity(4, 'Goofy'),
        ]);

        $comparator = new DummyEntityComparator();

        $unique = $this->unique($comparator);

        $unique->shouldNotBe($this);
        $unique->toArray()->shouldBeLike(
            [
                new DummyEntity(1, 'Joe'),
                new DummyEntity(2, 'William'),
                new DummyEntity(3, 'Jack'),
                new DummyEntity(4, 'Averell'),
            ]
        );
    }

    function it_returns_diff_using_string_comparison_by_default()
    {
        $items = ['item 1', 'item 2', 'item 3', 'item 4'];
        $this->beConstructedWith($items);

        $this->diff(new PlainArray([]))->toArray()->shouldBeLike($this->toArray());
        $this->diff(new PlainArray($items))->toArray()->shouldBeLike([]);
        $this->diff(new PlainArray(['item 1', 'item 4']))->toArray()->shouldBeLike(['item 2', 'item 3']);
        $this->diff(new PlainArray(['item 2', 'item 3']))->toArray()->shouldBeLike(['item 1', 'item 4']);
    }

    function it_returns_diff_using_provided_comparator()
    {
        $joe = new DummyEntity(1, 'Joe');
        $william = new DummyEntity(2, 'William');
        $jack = new DummyEntity(3, 'Jack');
        $averell = new DummyEntity(4, 'Averell');

        $this->beConstructedWith([$joe, $william, $jack, $averell]);

        $comparator = new DummyEntityComparator();

        $this->diff(new PlainArray([]), $comparator)->toArray()->shouldBeLike($this->toArray());
        $this->diff(new PlainArray([new DummyEntity(5, 'Ma')]), $comparator)
            ->toArray()->shouldBeLike([$joe, $william, $jack, $averell]);
        $this->diff(new PlainArray([$joe]), $comparator)->toArray()->shouldBeLike([$william, $jack, $averell]);
        $this->diff(new PlainArray([$averell]), $comparator)->toArray()->shouldBeLike([$joe, $william, $jack]);
        $this->diff(new PlainArray([$william, $jack]), $comparator)->toArray()->shouldBeLike([$joe, $averell]);
        $this->diff(new PlainArray([$joe, $william, $jack, $averell]), $comparator)->toArray()->shouldBeLike([]);
    }

    function it_returns_intersection_using_string_comparison_by_default()
    {
        $items = ['item 1', 'item 2', 'item 3', 'item 4'];
        $this->beConstructedWith($items);

        $this->intersect(new PlainArray([]))->toArray()->shouldBeLike([]);
        $this->intersect(new PlainArray($items))->toArray()->shouldBe($items);
        $this->intersect(new PlainArray(['item 1', 'item 4', 'item 5']))
            ->toArray()->shouldBeLike(['item 1', 'item 4']);
        $this->intersect(new PlainArray(['item 0', 'item 2', 'item 3', 'item 5']))
            ->toArray()->shouldBeLike(['item 2', 'item 3']);
    }

    function it_returns_intersection_using_provided_comparator()
    {
        $joe = new DummyEntity(1, 'Joe');
        $william = new DummyEntity(2, 'William');
        $jack = new DummyEntity(3, 'Jack');
        $averell = new DummyEntity(4, 'Averell');

        $items = [$joe, $william, $jack, $averell];
        $this->beConstructedWith($items);

        $comparator = new DummyEntityComparator();

        $this->intersect(new PlainArray([]), $comparator)->toArray()->shouldBeLike([]);
        $this->intersect(new PlainArray($items), $comparator)->toArray()->shouldBe($items);
        $this->intersect(new PlainArray([new DummyEntity(5, 'Ma')]), $comparator)
            ->toArray()->shouldBeLike([]);
        $this->intersect(new PlainArray([$joe]), $comparator)->toArray()->shouldBeLike([$joe]);
        $this->intersect(new PlainArray([$averell]), $comparator)->toArray()->shouldBeLike([$averell]);
        $this->intersect(new PlainArray([$william, $jack]), $comparator)
            ->toArray()->shouldBeLike([$william, $jack]);
        $this->intersect(new PlainArray($items), $comparator)
            ->toArray()->shouldBeLike($items);
    }

    function it_shuffles_items_returning_clone()
    {
        $this->beConstructedWith([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]);

        $shuffle = $this->shuffle();
        $shuffle->shouldNotBe($this);
        $shuffle->count()->shouldBeEqualTo($this->count());
        // Let`s trust it is shuffled...
    }

    function it_prepends_value()
    {
        $this->beConstructedWith(['item 1', 'item 2', 'item 3']);

        $clone = $this->unshift('item 0');
        $clone->shouldNotBe($this);
        $clone->toArray()->shouldBeLike(['item 0', 'item 1', 'item 2', 'item 3']);
    }

    function it_shifts_value_of_the_beginning_and_returns_reduced_array()
    {
        $this->beConstructedWith(['item 1', 'item 2', 'item 3']);

        $clone = $this->shift();
        $clone->shouldNotBe($this);
        $clone->toArray()->shouldBeLike(['item 2', 'item 3']);
    }

    function it_shifts_value_of_the_beginning_and_assigns_to_variable()
    {
        // No idea how to force PhpSpec to pass variable reference,
        // so here`s a little workaround.
        $items = ['item 1', 'item 2', 'item 3'];
        $this->beConstructedWith($items);
        $clone = new PlainArray($items);

        $reduced= $this->shift();
        $reduced->shouldBeLike($clone->shift($item));

        if ($item !== 'item 1') {
            throw new FailureException('Shifted value should be assigned to provided variable');
        }

        $reduced->count()->shouldReturn(2);
    }

    function it_pushes_value_to_the_end()
    {
        $this->beConstructedWith(['item 1', 'item 2', 'item 3']);

        $clone = $this->push('item 4');
        $clone->shouldNotBe($this);
        $clone->toArray()->shouldBeLike(['item 1', 'item 2', 'item 3', 'item 4']);
    }

    function it_pops_value_of_the_end_and_returns_reduced_array()
    {
        $this->beConstructedWith(['item 1', 'item 2', 'item 3']);

        $clone = $this->pop();
        $clone->shouldNotBe($this);
        $clone->toArray()->shouldBeLike(['item 1', 'item 2']);
    }

    function it_pops_value_of_the_end_and_assigns_to_variable()
    {
        $items = ['item 1', 'item 2', 'item 3'];
        $this->beConstructedWith($items);
        $twin = new PlainArray($items);

        $popped = $this->pop();
        $popped->shouldBeLike($twin->pop($item));

        if ($item !== 'item 3') {
            throw new FailureException('Popped value should be assigned to provided variable');
        }

        $this->count()->shouldReturn(3);
        $popped->count()->shouldReturn(2);
    }

    function it_reduces_array_of_numbers()
    {
        $this->beConstructedWith([10, 20, 50, 100]);

        $sum = function (int $sum, int $value): int {
            return $sum + $value;
        };

        $this->reduce($sum, 0)->shouldReturn(180);
    }

    function it_reduces_array_of_strings()
    {
        $this->beConstructedWith(['She', 'sells', 'sea', 'shells', 'by', 'the', 'sea', 'shore']);

        $join = function (string $reduced, string $value): string {
            return trim($reduced . ' ' . $value);
        };

        $this->reduce($join, '')->shouldReturn('She sells sea shells by the sea shore');
    }

    function it_returns_first_and_last_item()
    {
        $this->beConstructedWith(['item 1', 'item 2', 'item 3']);

        $this->first()->shouldReturn('item 1');
        $this->last()->shouldReturn('item 3');
    }

    function it_finds_first_item_that_matches_condition()
    {
        $this->beConstructedWith(['item 1', 'item 2', 'item 3', 'item 11', 'item 22', 'item 33']);

        $this
            ->find(function (string $item): bool {
                return strpos($item, '2') !== false;
            })
            ->shouldReturn('item 2');
    }

    function it_finds_last_item_that_matches_condition()
    {
        $this->beConstructedWith(['item 1', 'item 2', 'item 3', 'item 11', 'item 22', 'item 33']);

        $this
            ->findLast(function (string $item): bool {
                return strpos($item, '2') !== false;
            })
            ->shouldReturn('item 22');
    }

    function it_finds_null_when_no_item_matches_condition()
    {
        $this->beConstructedWith(['item 1', 'item 2', 'item 3']);

        $this
            ->find(function (string $item): bool {
                return strpos($item, 'x') !== false;
            })
            ->shouldReturn(null);

        $this
            ->findLast(function (string $item): bool {
                return strpos($item, 'x') !== false;
            })
            ->shouldReturn(null);
    }

    function it_returns_array_of_items()
    {
        $items = ['item 1', 'item 2', 'item 3'];
        $this->beConstructedWith($items);

        $this->toArray()->shouldReturn($items);
    }

    function it_is_iterable()
    {
        $this->beConstructedWith([]);
        $this->shouldImplement(\IteratorAggregate::class);
        $this->getIterator()->shouldHaveType(\Iterator::class);
    }

    function it_implements_ArrayAccess()
    {
        $items = ['item 1', 'item 2', 'item 3'];
        $this->beConstructedWith($items);

        $this->shouldImplement(\ArrayAccess::class);

        $this->offsetExists(0)->shouldReturn(true);
        $this->offsetGet(0)->shouldReturn('item 1');
        $this[0]->shouldBe('item 1');
    }

    function it_does_not_allow_mutations_trough_ArrayAccess()
    {
        $items = ['item 1', 'item 2', 'item 3'];
        $this->beConstructedWith($items);

        $this->shouldThrow(\BadMethodCallException::class)->during('offsetSet', [0, 'mutated 1']);
        $this->shouldThrow(\BadMethodCallException::class)->during('offsetUnset', [0]);
    }

    function it_can_be_converted_to_AssocValue()
    {
        $this->beConstructedWith(['item 0', 'item 1', 'item 2']);

        $this->toAssocValue()->toAssocArray()->shouldBeLike(['0' => 'item 0', '1' => 'item 1', '2' => 'item 2']);
    }

    function it_can_be_converted_to_StringsArray()
    {
        $this->beConstructedWith(['one', 'two', 'three']);

        $stringsArray = $this->toStringsArray();
        $stringsArray->shouldBeAnInstanceOf(StringsArray::class);
        $stringsArray->toNativeStrings()->shouldBeLike(['one', 'two', 'three']);
    }

    function it_can_be_converted_to_NumbersArray()
    {
        $this->beConstructedWith(['1', 2.1, new JustInteger(3)]);

        $numbersArray = $this->toNumbersArray();
        $numbersArray->shouldBeAnInstanceOf(NumbersArray::class);
        $numbersArray->toNativeNumbers()->shouldBeLike([1, 2.1, 3]);
    }

    function it_can_tell_if_has_element_or_not()
    {
        $this->beConstructedWith(['one', '2', 'three']);

        $this->hasElement('one')->shouldReturn(true);
        $this->hasElement('2')->shouldReturn(true);
        $this->hasElement(2)->shouldReturn(false);
        $this->hasElement('five')->shouldReturn(false);
    }
}
