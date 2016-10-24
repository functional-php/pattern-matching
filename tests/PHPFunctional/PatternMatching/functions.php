<?php

namespace tests\units;

use atoum;
use FunctionalPHP\PatternMatching as M;


class stdClass extends atoum
{
    /** @dataProvider splitEnclosedDataProvider */
    public function testSplitEnclosed($value, $expected)
    {
        $this->variable(M\split_enclosed(',', '[', ']', $value))->isEqualTo($expected);
    }

    public function splitEnclosedDataProvider()
    {
        return [
            ['', []],
            [' ', []],

            ['foo', ['foo']],
            ['foo,bar', ['foo', 'bar']],
            ['foo,bar,baz', ['foo', 'bar', 'baz']],

            [' foo ', ['foo']],
            [' foo,bar ', ['foo', 'bar']],
            ['foo , bar , baz', ['foo', 'bar', 'baz']],

            ['foo,', false],
            ['foo, ', false],
            [',foo', false],
            [' ,foo', false],

            [',', false],
            [' , ', false],

            ['[foo,bar],baz,qux', ['[foo,bar]', 'baz', 'qux']],
            ['foo,[bar,baz],qux', ['foo', '[bar,baz]', 'qux']],
            ['foo,bar,[baz,qux]', ['foo', 'bar', '[baz,qux]']],
            ['foo[,[bar,baz],]qux', ['foo[,[bar,baz],]qux']],
        ];
    }

    public function testNoPatterns()
    {
        $this->exception(function() { M\match('some value', []); })
             ->hasMessage('Non-exhaustive patterns.')
             ->isInstanceOf('\RuntimeException');
    }

    public function testNoMatch()
    {
        $this->exception(function() { M\match('some value', ['"other text"' => function() {}]); })
             ->hasMessage('Non-exhaustive patterns.')
             ->isInstanceOf('\RuntimeException');
    }

    /** @dataProvider matchDataProvider */
    public function testMatch($value, $pattern, $expected)
    {
        $function = function() { return func_get_args(); };

        $this->variable(M\match($value, [$pattern => $function]))->isEqualTo($expected);
    }

    public function matchDataProvider()
    {
        return [
            [10, 'a', [10]],
            [[1, 2, 3], '(x:xs)', [1, [2, 3]]],
        ];
    }

    /** @dataProvider matchDataProvider */
    public function testConst($value, $pattern, $expected)
    {
        $this->variable(M\match($value, [$pattern => $expected]))->isEqualTo($expected);
    }

    /** @dataProvider extractDataProvider */
    public function testExtract($value, $pattern, $expected)
    {
        $this->variable(M\extract($value, $pattern))->isEqualTo($expected);
    }

    public function extractDataProvider()
    {
        return [
            [10, 'a', ['a' => 10]],
            [[1, 2, 3], '(x:xs)', ['x' => 1, 'xs' => [2, 3]]],
            [[1, 2, 3], 'all@[a, b, c]', ['all' => [1, 2, 3], 'a' => 1, 'b' => 2, 'c' => 3]],
        ];
    }
}
