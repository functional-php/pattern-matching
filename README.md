# Pattern Matching

[![Build Status](https://travis-ci.org/functional-php/pattern-matching.svg)](https://travis-ci.org/functional-php/pattern-matching)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/functional-php/pattern-matching/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/functional-php/pattern-matching/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/functional-php/pattern-matching/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/functional-php/pattern-matching/?branch=master)
[![Average time to resolve an issue](http://isitmaintained.com/badge/resolution/functional-php/pattern-matching.svg)](http://isitmaintained.com/project/functional-php/pattern-matching "Average time to resolve an issue")
[![Percentage of issues still open](http://isitmaintained.com/badge/open/functional-php/pattern-matching.svg)](http://isitmaintained.com/project/functional-php/pattern-matching "Percentage of issues still open")
[![Chat on Gitter](https://img.shields.io/gitter/room/gitterHQ/gitter.svg)](https://gitter.im/functional-php)

Pattern matching is the process of checking a series of token against a pattern.
It is different from pattern recognition as the match needs to be exact.
The process does not only match as a switch statement does, it also assigns the value
a bit like the ``list`` construct in PHP, a process called **destructuring**.

Most functional languages implement it as a core feature. Here is are some small examples in Haskell:

``` haskell

fact :: (Integral a) => a -> a
fact 0 = 1
fact n = n * fact (n-1)

head :: [a] -> a
head xs = case xs of []    -> error "empty list"
                     (x:_) -> x

firstThree :: [a] -> (a, a, a)
firstThree (x:y:z:_) = (x, y, z)
firstThree _ = error "need at least 3 elements"

```

If you want to read more about the topic, you can head over to Wikipedia : [Pattern matching](https://en.wikipedia.org/wiki/Pattern_matching)

## Installation

    composer require functional-php/pattern-matching

## Basic Usage

As we cannot extend the syntax of PHP, the choice was made to use a syntax based on arrays.
The key representes the pattern and the value is the function to call with the value or a constant if
you want to do nothing with it.

Let's see how we could implement our 3 Haskell examples:

```php

use FunctionalPHP\PatternMatching as m;

$fact = m\func([
    '0' => 1,
    'n' => function($n) use(&$fact) {
        return $n * $fact($n - 1);
    }
]);

$head = m\func([
    '(x:_)' => function($x) { return $x; },
    '_' => function() { throw new RuntimeException('empty list'); }
]);

$firstThree= m\func([
    '(x:y:z:_)' => function($x, $y, $z) { return [$x, $y, $z]; },
    '_' => function() { throw new RuntimeException('need at least 3 elements'); }
]);

```

You can also use the `match` function if you want to have a beefed up version of the `switch` statement or if you don't like anonymous functions:

```php

use FunctionalPHP\PatternMatching as m;

function factorial($n) {
    return m\match($n, [
        '0' => 1,
        'n' => function($n) use(&$fact) {
            return $n * factorial($n - 1);
        }
    ]);
}

echo m\match([1, 2, ['a', 'b'], true], [
    '"toto"' => 'first',
    '[a, [b, c], d]' => 'second',
    '[a, _, (x:xs), c]' => 'third',
    '_' => 'default',
]);
// third

```

If you are just interested in destructuring your values, there is also a helper for that:

``` php

use FunctionalPHP\PatternMatching as m;

print_r(m\extract([1, 2, ['a', 'b'], true], '[a, _, (x:xs), c]'));
// Array (
//     [a] => 1
//     [x] => a
//     [xs] => Array ( [0] => b )
//     [c] => 1
// )

```

## Patterns

Here is a quick recap of the available patterns:

| Name          | Format                            | Example                         |
|---------------|-----------------------------------|---------------------------------|
| Constant      | Any scalar value (int, float, string, boolean)    | ``1.0``, ``42``, "test"         |
| Variable      | ``identifier``                    | ``a``, ``name``, ``anything``   |
| Array         | ``[<pattern>, ..., <pattern>]``   | ``[]``, ``[a]``, ``[a, b, c]``  |
| Cons          | ``(identifier:list-identifier)``  | ``(x:xs)``, ``(x:y:z:xs)``      |
| Wildcard      | ``_``                             | ``_``                           |
| As            | ``identifier@(<pattern>)``        | ``all@(x:xs)``                  |

## Testing

You can run the test suite for the library using:

    composer test
    
A test report will be available in the `reports` directory.

## Contributing

Any contribution welcome :

- Ideas
- Pull requests
- Issues
