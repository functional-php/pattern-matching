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

    public function testNoMatchingPattern()
    {
        $this->exception(function() { M::match('some value', ['something else' => function() {}]); })
             ->hasMessage('Non-exhaustive patterns.')
             ->isInstanceOf('\RuntimeException');
    }
}

