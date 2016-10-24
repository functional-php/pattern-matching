<?php

namespace PHPFunctional\PatternMatching\tests\units;

use atoum;
use PHPFunctional\PatternMatching\Matcher as M;


class Matcher extends atoum
{
    public function testNoPatterns()
    {
        $this->exception(function() { M::match('some value', []); })
             ->hasMessage('Non-exhaustive patterns.')
             ->isInstanceOf('\RuntimeException');
    }

    /** @dataProvider noMatchingDataProvider */
    public function testNoMatchingPattern($value, $pattern)
    {
        $this->exception(function() use($value, $pattern) { M::match($value, [$pattern => function() {}]); })
             ->hasMessage('Non-exhaustive patterns.')
             ->isInstanceOf('\RuntimeException');
    }

    public function noMatchingDataProvider()
    {
        return [
            ['some value', "not a valid pattern"],
            ['not an array', '[]'],
            [[1, 2], '[1, 2, 3]'],
            [[1], '[[]]'],
            [[[1]], '[[a, b]]'],
        ];
    }

    /** @dataProvider constantDataProvider */
    public function testConstant($value, $pattern)
    {
        $function = function() { return 'matched'; };

        $this->string(M::match($value, [$pattern => $function]))->isEqualTo('matched');
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
            [True, 'true'], [True, 'True'], [True, 'TRUE'],
            [TRUE, 'true'], [TRUE, 'True'], [TRUE, 'TRUE'],

            [false, 'false'], [false, 'False'], [false, 'FALSE'],
            [False, 'false'], [False, 'False'], [False, 'FALSE'],
            [FALSE, 'false'], [FALSE, 'False'], [FALSE, 'FALSE'],
        ];
    }

    /** @dataProvider identifierDataProvider */
    public function testIdentifier($value)
    {
        $function = function($a) { return $a; };

        $this->variable(M::match($value, ['a' => $function]))->isEqualTo($value);
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
        $function = function() { return count(func_get_args()); };

        $this->variable(M::match($value, ['_' => $function]))->isEqualTo(0);
    }

    /** @dataProvider arrayDataProvider */
    public function testArray($value, $pattern, $expected)
    {
        $function = function() { return array_sum(func_get_args()); };

        $this->variable(M::match($value, [$pattern => $function]))->isEqualTo($expected);
    }

    public function arrayDataProvider()
    {
        return [
            [ [], '[]', 0],
            [ [1], '[a]', 1],
            [ [1, 2, 3, 4], '[a, b, c, d]', 10],
            [ [1, 2, 3, 4], '[a, 2, c, d]', 8],
            [ [1, 2, 3, 4], '[a, b, _, d]', 7],
            [ [[1, 2], [3, 4]], '[[a, b], [c, d]]', 10],
            [ [[1, 2], [3, 4]], '[[_, b], [c, d]]', 9],
            [ [[1, 2], [3, 4]], '[[a, b], [c, 4]]', 6],
            [ [[1, [2, 3], 4]], '[[a, [b, c], 4]]', 6],
            [ [[[[[1]]]], 2], '[[[[[a]]]], b]', 3],
            [ [[[[[1]], 2]], 3], '[[[[[1]], a]], b]', 5],
        ];
    }
}

