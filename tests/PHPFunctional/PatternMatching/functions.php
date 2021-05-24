<?php

namespace tests\units;

use atoum;
use FunctionalPHP\PatternMatching as M;

class stdClass extends atoum
{
    /** @dataProvider splitEnclosedDataProvider */
    public function testSplitEnclosed($value, $expected)
    {
        $this->variable(M\split_enclosed(',', '[', ']', $value))->isIdenticalTo($expected);
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
        $this->exception(function () {
            M\pmatch([], 'some value');
        })
             ->hasMessage('Non-exhaustive patterns.')
             ->isInstanceOf('\RuntimeException');
    }

    public function testNoMatch()
    {
        $this->exception(function () {
            M\pmatch(['"other text"' => function () {
            }], 'some value');
        })
             ->hasMessage('Non-exhaustive patterns.')
             ->isInstanceOf('\RuntimeException');
    }

    /** @dataProvider matchDataProvider */
    public function testMatch($value, $pattern, $expected)
    {
        $function = function () {
            return func_get_args();
        };

        $this->variable(M\pmatch([$pattern => $function], $value))->isIdenticalTo($expected);
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
        $this->variable(M\pmatch([$pattern => $expected], $value))->isIdenticalTo($expected);
    }

    /** @dataProvider matchDataProvider */
    public function testCurryConst($value, $pattern, $expected)
    {
        $curryied = M\pmatch([$pattern => $expected]);

        $this->variable($curryied)->isCallable();
        $this->variable($curryied($value))->isIdenticalTo($expected);
    }

    /** @dataProvider extractDataProvider */
    public function testExtract($value, $pattern, $expected)
    {
        $this->variable(M\extract($pattern, $value))->isIdenticalTo($expected);
    }

    /** @dataProvider extractDataProvider */
    public function testCurryExtract($value, $pattern, $expected)
    {
        $curryied = M\extract($pattern);

        $this->variable($curryied)->isCallable();
        $this->variable($curryied($value))->isIdenticalTo($expected);
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
