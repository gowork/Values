<?php

namespace spec\GW\Value;

use GW\Value\Filters;
use GW\Value\Wrap;
use GW\Value\AssocArray;
use PhpSpec\ObjectBehavior;

final class AssocArraySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([
            'a' => 'alf',
            'b' => 'berni',
            'c' => 'clifford',
        ]);
    }

    function it_is_initializable()
    {
        $this->beConstructedWith([]);
        $this->shouldHaveType(AssocArray::class);
    }

    function it_should_return_keys()
    {
        $keys = $this->keys();
        $keys->shouldNotBe($this);
        $keys->toArray()->shouldBeLike(['a', 'b', 'c']);
    }

    function it_should_return_filtered_array()
    {
        $this->beConstructedWith([
            'a' => 'alf',
            'b' => '',
            'c' => 'clifford',
        ]);

        $filtered = $this->filterEmpty();
        $filtered->shouldNotBe($this);
        $filtered->toAssocArray()->shouldBeLike(
            [
                'a' => 'alf',
                'c' => 'clifford',
            ]
        );
    }

    function it_should_return_number_of_elements()
    {
        $this->count()->shouldBe(3);
    }

    function it_return_array_in_reversed_order()
    {
        $reversed = $this->reverse();
        $reversed->shouldNotBe($this);
        $reversed->toAssocArray()->shouldBeLike(
            [
                'c' => 'clifford',
                'b' => 'berni',
                'a' => 'alf',
            ]
        );
    }

    function it_return_shuffled_array()
    {
        $shuffled = $this->shuffle();
        $shuffled->shouldNotBe($this);
        $shuffled->toAssocArray()->shouldNotBeLike(
            [
                'a' => 'alf',
                'b' => 'berni',
                'c' => 'clifford',
            ]
        );
    }

    function it_return_unique_values()
    {
        $this->beConstructedWith(['a' => 'a', 'b' => 'a', 'c' => 'a']);
        $this->unique()->toAssocArray()->shouldBeLike(['a' => 'a']);
    }

    function it_can_return_clone_with_unique_values_comparing_them_using_comparator()
    {
        $this->beConstructedWith([
            'a' => new DummyEntity(1, 'Joe'),
            'b' => new DummyEntity(1, 'Joey'),
            'c' => new DummyEntity(2, 'William'),
            'd' => new DummyEntity(2, 'Will'),
            'e' => new DummyEntity(3, 'Jack'),
            'f' => new DummyEntity(4, 'Averell'),
            'g' => new DummyEntity(4, 'Goofy'),
        ]);

        $comparator = new DummyEntityComparator();

        $unique = $this->unique($comparator);

        $unique->shouldNotBe($this);
        $unique->toAssocArray()->shouldBeLike(
            [
                'a' => new DummyEntity(1, 'Joe'),
                'c' => new DummyEntity(2, 'William'),
                'e' => new DummyEntity(3, 'Jack'),
                'f' => new DummyEntity(4, 'Averell'),
            ]
        );
    }

    function it_can_merge_two_arrays()
    {
        $merged = $this->merge(new AssocArray(['d' => 'dummy']));
        $merged->shouldNotBe($this);
        $merged->toAssocArray()->shouldBeLike(
            [
                'a' => 'alf',
                'b' => 'berni',
                'c' => 'clifford',
                'd' => 'dummy'
            ]
        );
    }

    function it_can_add_element()
    {
        $this->beConstructedWith(['a' => 'foo', 'b' => 'bar']);
        $this->with('c', 'baz')->toAssocArray()->shouldEqual(['a' => 'foo', 'b' => 'bar', 'c' => 'baz']);
    }

    function it_can_replace_element()
    {
        $this->beConstructedWith(['a' => 'foo', 'b' => 'bar']);
        $this->with('b', 'baz')->toAssocArray()->shouldEqual(['a' => 'foo', 'b' => 'baz']);
    }

    function it_can_replace_int_element()
    {
        $this->beConstructedWith(['foo', 'bar']);
        $this->with(1, 'baz')->toAssocArray()->shouldEqual(['foo', 'baz']);
    }

    function it_can_delete_element_by_key()
    {
        $array = $this->without('a');
        $array->shouldNotBe($this);
        $array->toAssocArray()->shouldBeLike(
            [
                'b' => 'berni',
                'c' => 'clifford',
            ]
        );
    }

    function it_can_create_a_copy_without_multiple_keys()
    {
        $array = $this->without('a', 'c');
        $array->shouldNotBe($this);
        $array->toAssocArray()->shouldBeLike(['b' => 'berni']);
    }

    function it_can_create_a_copy_without_multiple_int_keys()
    {
        $this->beConstructedWith(['a', 'b', 'c', 'd']);
        $array = $this->without(1, 3);
        $array->shouldNotBe($this);
        $array->toAssocArray()->shouldBeLike([0 => 'a', 2 => 'c']);
    }

    function it_can_create_a_copy_with_only_given_set_of_keys()
    {
        $array = $this->only('a', 'c');

        $array->shouldNotBe($this);
        $array->toAssocArray()->shouldBeLike(['a' => 'alf', 'c' => 'clifford']);

        $this->only('a')->toAssocArray()->shouldBeLike(['a' => 'alf']);
    }

    function it_can_delete_element_by_value()
    {
        $array = $this->withoutElement('alf');
        $array->shouldNotBe($this);
        $array->toAssocArray()->shouldBeLike(
            [
                'b' => 'berni',
                'c' => 'clifford',
            ]
        );
    }

    function it_can_return_value_by_key()
    {
        $this->get('a')->shouldBe('alf');
        $this->get('z', 'zorro')->shouldBe('zorro');
    }

    function it_can_check_if_element_exists_by_key()
    {
        $this->has('a')->shouldBe(true);
        $this->has('z')->shouldBe(false);
    }

    function it_can_return_first_value()
    {
        $this->first()->shouldBe('alf');
    }

    function it_can_return_last_value()
    {
        $this->last()->shouldBe('clifford');
    }

    function it_can_find_matching_value()
    {
        $this->beConstructedWith(['one', 'two', 'three', 'four', 'five', 'six']);

        $fourChars = function (string $value): bool {
            return \strlen($value) === 4;
        };

        $this->find($fourChars)->shouldReturn('four');
        $this->findLast($fourChars)->shouldReturn('five');
    }

    function it_can_return_array()
    {
        $this->toArray()->shouldBe(['alf', 'berni', 'clifford']);
    }

    function it_can_return_iterator()
    {
        $this->getIterator()->shouldBeAnInstanceOf(\Iterator::class);
    }

    function it_can_execute_callback_on_each_element(CallableMock $callableMock)
    {
        $callableMock->__invoke('alf', 'a')->shouldBeCalled();
        $callableMock->__invoke('berni', 'b')->shouldBeCalled();
        $callableMock->__invoke('clifford', 'c')->shouldBeCalled();

        $this->each($callableMock);
    }

    function it_transforms_all_keys_to_strings()
    {
        $this->beConstructedWith(['zero', 'one', 'two']);

        $this->keys()->toArray()->shouldBeLike(['0', '1', '2']);
    }

    function it_can_be_created_from_array()
    {
        $this->beConstructedThrough('fromArray', [['a' => 'alf', 'b' => 'bernie']]);
    }

    function it_can_be_map_values_with_transformer_callback()
    {
        $mapped = $this->map(function (string $value, string $key): string {
            return "{$key}: {$value}";
        });

        $mapped->shouldNotBe($this);
        $mapped->toAssocArray()->shouldBeLike(
            [
                'a' => 'a: alf',
                'b' => 'b: berni',
                'c' => 'c: clifford',
            ]
        );
    }

    function it_can_map_keys_with_transformer_callback()
    {
        $mapped = $this->mapKeys(function (string $key, string $value): string {
            return "{$key}: {$value}";
        });

        $mapped->shouldNotBe($this);
        $mapped->toAssocArray()->shouldBeLike(
            [
                'a: alf' => 'alf',
                'b: berni' => 'berni',
                'c: clifford' => 'clifford',
            ]
        );
    }

    function it_can_filter_with_callback_filter()
    {
        $filtered = $this->filter(function (string $value): bool {
            return $value === 'alf';
        });

        $filtered->shouldNotBe($this);
        $filtered->toAssocArray()->shouldBeLike(['a' => 'alf']);
    }

    function it_can_be_sorted_with_comparator()
    {
        $reversed = $this->sort(function(string $a, string $b): int {
            return $b <=> $a;
        });

        $reversed->shouldNotBe($this);
        $reversed->toAssocArray()->shouldBeLike(
            [
                'c' => 'clifford',
                'b' => 'berni',
                'a' => 'alf',
            ]
        );
    }

    function it_can_be_sorted_by_key_with_comparator()
    {
        $reversed = $this->sortKeys(function(string $a, string $b): int {
            return $b <=> $a;
        });

        $reversed->shouldNotBe($this);
        $reversed->toAssocArray()->shouldBeLike(
            [
                'c' => 'clifford',
                'b' => 'berni',
                'a' => 'alf',
            ]
        );
    }

    function it_can_reduce_items_with_callback()
    {
        $this->reduce(
            function(string $reduced, string $value, string $key): string {
                return $reduced . "{$key}: {$value} ";
            },
            ''
        )->shouldReturn('a: alf b: berni c: clifford ');
    }

    function it_can_tell_if_is_not_empty()
    {
        $this->isEmpty()->shouldReturn(false);
    }

    function it_can_tell_if_is_empty()
    {
        $this->beConstructedWith([]);

        $this->isEmpty()->shouldReturn(true);
    }

    function it_can_tell_if_has_element_or_not()
    {
        $this->beConstructedWith(['one' => 'one', 'two' => '2', '3' => 'three']);

        $this->hasElement('one')->shouldReturn(true);
        $this->hasElement('2')->shouldReturn(true);
        $this->hasElement(2)->shouldReturn(false);
        $this->hasElement('five')->shouldReturn(false);
    }

    function it_allows_to_check_if_any_element_satisfies_filter_condition()
    {
        $this->beConstructedWith(['a' => 2, 'b' => 4, 'c' => 6, 'd' => 8, 'e' => 10, 'f' => 12, 'g' => 14, 'h' => 16]);

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
        $this->beConstructedWith(['a' => 2, 'b' => 4, 'c' => 6, 'd' => 8, 'e' => 10, 'f' => 12, 'g' => 14, 'h' => 16]);

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

    function it_implements_ArrayAccess()
    {
        $items = ['a' => 'item 1', 'b' => 'item 2', 'c' => 'item 3'];
        $this->beConstructedWith($items);

        $this->shouldImplement(\ArrayAccess::class);

        $this->offsetExists('a')->shouldReturn(true);
        $this->offsetGet('b')->shouldReturn('item 2');
        $this['a']->shouldBe('item 1');
    }

    function it_does_not_allow_mutations_trough_ArrayAccess()
    {
        $items = ['a' => 'item 1', 'b' => 'item 2', 'c' => 'item 3'];
        $this->beConstructedWith($items);

        $this->shouldThrow(\BadMethodCallException::class)->during('offsetSet', ['a', 'mutated 1']);
        $this->shouldThrow(\BadMethodCallException::class)->during('offsetUnset', ['a']);
    }
}
