<?php

namespace PHPFunctional\PatternMatching;

function split_enclosed($delimiter, $open, $close, $string)
{
    $chars = str_split($string);

    $result = [];
    $buffer = '';
    $level = 0;
    foreach($chars as $c) {
        if($c === $delimiter && $level === 0) {
            $result[] = $buffer;
            $buffer = '';
        } else {
            $buffer .= $c;

            if($c === $open) {
                ++$level;
            } else if($c === $close) {
                --$level;
            }
        }
    }

    if(strlen($buffer) > 0) {
        $result[] = $buffer;
    }

    return $result;
}
