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

    public function testNoMatch()
    {
        $this->exception(function() { M::match('some value', ['"other text"' => function() {}]); })
             ->hasMessage('Non-exhaustive patterns.')
             ->isInstanceOf('\RuntimeException');
    }

    /** @dataProvider matchDataProvider */
    public function testMatch($value, $pattern, $expected)
    {
        $function = function() { return func_get_args(); };

        $this->variable(M::match($value, [$pattern => $function]))->isEqualTo($expected);
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
        $this->variable(M::match($value, [$pattern => $expected]))->isEqualTo($expected);
    }
}
