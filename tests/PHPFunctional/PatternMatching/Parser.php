<?php

namespace FunctionalPHP\PatternMatching\tests\units;

use atoum;

class Parser extends atoum
{
    /** @dataProvider noMatchingPatternDataProvider */
    public function testNoMatchingPattern($value, $pattern)
    {
        $this->boolean($this->newTestedInstance->parse($pattern, $value))->isFalse();
    }

    public function noMatchingPatternDataProvider()
    {
        return [
            [0, '10'], [0, '-10'], [0, '1.0'],
            [0.0, '10'], [0.0, '-10'], [0.0, '1.0'],
            [5, '10'], [5, '-10'], [5, '1.0'],
            [5.3, '10'], [5.3, '-10'], [5.3, '1.0'],
            [true, 'false'],
            ['true', 'true'],
            [false, 'true'],
            ['false', 'false'],
            ['some value', '"not a matching pattern"'],
            ['not an array', '[]'],
            [[1, 2], '[1, 2, 3]'],
            [[1], '[[]]'],
            [[[1]], '[[a, b]]'],
            [ 'not an array', '(x:xs)'],
            [ [], '(x:xs)'],
            [ [1], '([a, b]:xs)'],
            [ [1], 'all@[a, b]'],

            ['toto', '0'],
            ['toto', '0.0'],
            ['toto', 'true'],
            ['toto', '"plop"'],
            ['toto', '[a, b, c]'],
            ['toto', '(x:xs)'],
            ['toto', 'all@(x:xs)'],
        ];
    }

    /** @dataProvider invalidPatternProvider */
    public function testInvalidPattern($pattern)
    {
        $this->exception(function () use ($pattern) {
            $this->newTestedInstance->parse($pattern, '');
        })->isInstanceOf('\RuntimeException')
          ->message->contains('Invalid pattern');
    }

    public function invalidPatternProvider()
    {
        return [
            ['(x:)'], ['(x:xs'], ['x:xs'], ['x:xs)'], ['(:xs)'], ['(:)'], ['(x::xs)'],
            ['[a, ]'], ['[, b]'], ['[ , ]'], ['[a, , b'], ['[a,]'], ['[,b]'], ['[,]'],

            ['all@'], ['_@(x:xs)'], ['10@(x:xs)'], ['"test"@(x:xs)'],
            ['[a, b]@(x:xs)'], ['(x:xs)@[c, d]'], // ['[a, b]@[c, d]'], ['(x:xs)@(x:xs)'],
        ];
    }

    /** @dataProvider nonUniquePatternProvider */
    public function testNonUniquePattern($pattern)
    {
        $this->exception(function () use ($pattern) {
            $this->newTestedInstance->parse($pattern, [1, 2, 3, 4]);
        })->isInstanceOf('\RuntimeException')
          ->message->contains('Non unique identifiers');
    }

    public function nonUniquePatternProvider()
    {
        return [
            ['(x:x)'], ['(x:y:x)'],
            ['[a, a]'], ['[a, b, a]'],
            ['all@(all:xs)'], ['all@(x:all)'], ['all@(all:all)'],
        ];
    }

    /** @dataProvider constantDataProvider */
    public function testConstant($value, $pattern)
    {
        $this->array($this->newTestedInstance->parse($pattern, $value))->isEmpty();
    }

    public function constantDataProvider()
    {
        return [
            [0, '0'],
            [10, '10'],
            [-10, '-10'],

            [0.0, '0.0'],
            [1.42, '1.42'], [1.42, '1.42'],
            [-1.42, '-1.42'],

            ['test', '"test"'], ['test', "'test'"],
            ['test test', '"test test"'], ['test test', "'test test'"],

            [true, 'true'], [true, 'True'], [true, 'TRUE'],
            [false, 'false'], [false, 'False'], [false, 'FALSE'],
        ];
    }

    /** @dataProvider identifierDataProvider */
    public function testIdentifier($value)
    {
        $this->array($this->newTestedInstance->parse('a', $value))
            ->isIdenticalTo(['a' => $value]);
        $this->array($this->newTestedInstance->parse('longIdentifier', $value))
            ->isIdenticalTo(['longIdentifier' => $value]);
    }

    public function identifierDataProvider()
    {
        return [
            ['test'], [10], [[1, 2, 3, 4]], [true], [false], [null]
        ];
    }

    /** @dataProvider identifierDataProvider */
    public function testWildcard($value)
    {
        $this->array($this->newTestedInstance->parse('_', $value))->isEmpty();
    }

    /** @dataProvider arrayDataProvider */
    public function testArray($value, $pattern, $expected)
    {
        $this->given($results = $this->newTestedInstance->parse($pattern, $value))
             ->then
                ->array(array_values($results))
                ->isIdenticalTo($expected);
    }

    public function arrayDataProvider()
    {
        return [
            [ [], '[]', []],
            [ [1], '[a]', [1]],
            [ [1, 2, 3, 4], '[a, b, c, d]', [1, 2, 3, 4]],
            [ [1, 2, 3, 4], '[a, 2, c, d]', [1, 3, 4]],
            [ [1, 2, 3, 4], '[a, b, _, d]', [1, 2, 4]],
            [ [[1, 2], [3, 4]], '[[a, b], [c, d]]', [1, 2, 3, 4]],
            [ [[1, 2], [3, 4]], '[[_, b], [c, d]]', [2, 3, 4]],
            [ [[1, 2], [3, 4]], '[[a, b], [c, 4]]', [1, 2, 3]],
            [ [[1, [2, 3], 4]], '[[a, [b, c], 4]]', [1, 2, 3]],
            [ [[[[[1]]]], 2], '[[[[[a]]]], b]', [1, 2]],
            [ [[[[[1]], 2]], 3], '[[[[[1]], a]], b]', [2, 3]],
        ];
    }

    /** @dataProvider consDataProvider */
    public function testCons($value, $pattern, $expected)
    {
        $this->given($results = $this->newTestedInstance->parse($pattern, $value))
             ->then
                ->array(array_values($results))
                ->isIdenticalTo($expected);
    }

    public function consDataProvider()
    {
        return [
            [ [1], '(x:xs)', [1, []] ],
            [ [1], '(_:xs)', [[]] ],
            [ [1, 2, 3, 4], '(x:xs)', [1, [2, 3, 4]] ],
            [ [1, 2, 3, 4], '(x:y:xs)', [1, 2, [3, 4]] ],
            [ [1, 2, 3, 4], '(x:y:z:xs)', [1, 2, 3, [4]] ],
            [ [1, 2, 3, 4], '(x:2:z:xs)', [1, 3, [4]] ],
            [ [1, 2, 3, 4], '(x:y:_:xs)', [1, 2, [4]] ],
            [ [1, [2, 3, 4]], '[a, (x:xs)]', [1, 2, [3, 4]] ],
            [ [[1, 2, 3], 4], '((x:xs):ys)', [1, [2, 3], [4]] ],
            [ [1], '(x:[])', [1] ],
            [ [1, 2, 3], '(x:[a, b])', [1, 2, 3] ],
            [ [1], '(x:_)', [1] ],
        ];
    }

    /** @dataProvider asDataProvider */
    public function testAs($value, $pattern, $expected)
    {
        $this->given($results = $this->newTestedInstance->parse($pattern, $value))
             ->then
                ->array(array_values($results))
                ->isIdenticalTo($expected);
    }

    public function asDataProvider()
    {
        return [
            [ [1], 'all@(x:xs)', [[1], 1, []] ],
            [ [1, 2, 3], 'all@(x:xs)', [[1, 2, 3], 1, [2, 3]] ],
            [ [], 'all@a', [[], []] ],
            [ [], 'all@_', [[]] ],
            [ [1, 2, 3], 'all@a', [[1, 2, 3], [1, 2, 3]] ],
            [ [], 'all@[]', [[]] ],
            [ [1, 2, 3], 'all@[a, b, c]', [[1, 2, 3], 1, 2, 3] ],
        ];
    }
}
