# Values

*Values* is a library to wrap PHP's primitive types into clean, immutable and more user-friendly objects.

[![Build Status](https://travis-ci.org/gowork/values.svg?branch=master)](https://travis-ci.org/gowork/values)

## Installation

It works on PHP >=7.1. This library is available on Composer/Packagist as `gowork/values`. To install it execute:

```shell
composer require gowork/values ^0.1
```

or manually update your `composer.json` with:
```json
{
    (...)
    "require": {
        "gowork/values": "^0.1"
    }
    (...)
}
```

and run `composer install` or `composer update` afterwards. If you are not using Composer, download sources from GitHub and load them as required. However, using Composer is highly recommended.
 
## Usage

Currently available implementations are:

### `ArrayValue`

Object equivalent of PHP native indexed array. It contains implementation of most `array_*` functions as object method.

Example:
```php
<?php

use GW\Value\Wrap;

$arrayValue = Wrap::array(['a', 'b', 'c', 'a', 'd', 'f'])
    ->map(function (string $value): string {
        return strtoupper($value)
    })
    ->map('strtolower')
    ->filter(function (string $value): bool {
        return $value !== 'd';
    })
    ->sort(function (string $a, string $b): int {
        return $a <=> $b;
    })
    ->shuffle()
    ->reverse()
    ->unique()
    ->diff(Wrap::array(['d', 'f']))
    ->intersect(Wrap::array(['a', 'b', 'c']))
    ->join(Wrap::array(['g', 'h', 'i']))
    ->unshift('j')
    ->shift($j)
    ->push('l')
    ->pop($l)
    ->slice(0, 6)
    ->each(function (string $value): void {
        echo $value;
    });

$count = $arrayValue->count();

$reduced = $arrayValue->reduce(
    function (string $reduced, string $value): string {
        return $reduced . $value;
    },
    ''
);

$stringValue = $arrayValue->implode(', ');

if (isset($arrayValue[0])) {
    $first = $arrayValue[0];
}

$first = $arrayValue->first();

foreach ($arrayValue as $item) {
    echo $item;
}
```

### `AssocValue`

Object equivalent of PHP associative array. It has all the methods of `ArrayValue` with few minor differences and few additions.

```php
<?php

use \GW\Value\Wrap;

$assocValue = Wrap::assocArray(['a' => 1, 'b' => 2, 'c' => 3, 'x' => 0])
    ->with('d', 4)
    ->without('a', 'b')
    ->withoutElement(0)
    ->merge(Wrap::assocArray(['e' => 5, 'f' => 6]));

$keys = $assocValue->keys();

$withMappedKeys = $assocValue->mapKeys(function (string $key): string {
    return strtoupper($key);
});

$aValue = $assocValue->get('a', $default = 1);
$hasA = $assocValue->has('a');

$associativeArray = $assocValue->toAssocArray();
$indexedArray = $assocValue->toArray();
```

### `StringValue`

Object equivalent of PHP primitive string. It contains implementation of most `str*`/`mb_str*` functions as object method.

```php
<?php

use GW\Value\Wrap;

$stringValue = Wrap::string('just example string')
    ->trim()
    ->trimRight()
    ->trimLeft()
    ->lower()
    ->upper()
    ->lowerFirst()
    ->upperFirst()
    ->upperWords()
    ->padLeft(50, '-')
    ->padRight(100, '-')
    ->padBoth(200, '-')
    ->replace('no', 'yes')
    ->replacePattern('/\s/', '-')
    ->replacePatternCallback('/[\-]+/', function (array $match): string {
        return '-';
    })
    ->truncate(140)
    ->substring(0, 100)
    ->stripTags();

$hasExample = $stringValue->contains('example');
$firstA = $stringValue->position('a');
$lastA = $stringValue->positionLast('a');
$stringLength = $stringValue->length();
$primitiveString = $stringValue->toString();
$onlyLetters = $stringValue->isMatching('/^[a-z]+$/');
$stringsArray = $stringValue->explode(' ');
```

### `StringsArray`

Object wrapping array of strings. It has all methods of `ArrayValue` and `StringValue`. 
Calling a method inherited from `StringValue` means is same as calling this method on each `StringValue` element contained in `StringsArray`.

```php
<?php

use \GW\Value\Wrap;
use \GW\Value\StringValue;

$stringsArray = Wrap::stringsArray(['one', '  two ', '<b>three</b>'])
    // StringValue
    ->trim()
    ->stripTags()
    ->padLeft(16)
    // ArrayValue
    ->unique()
    ->each(function (StringValue $value): void {
        echo $value->toString();
    });
```

## Documentation

For full methods reference and more examples see [here](./docs/examples.md).

## Contributing

Want to contribute? Perfect! Submit an issue or Pull Request and explain what would you like to see in `GW/Value`.

## License

MIT license. See LICENSE file in the main directory of this repository.
