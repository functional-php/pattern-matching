<?php

namespace FunctionalPHP\PatternMatching;

/**
 * Destructure the given value using the given pattern, then returns
 * the resulting values as an array indexed using the identifiers of
 * the pattern.
 *
 * If the extraction failed, will return False.
 *
 * @param mixed $value
 * @param string $pattern
 * @return array|bool
 */
function extract($value, $pattern)
{
    return (new Parser())->parse($value, $pattern);
}

/**
 * Given a value and an array with the format <pattern> => <callback>,
 * matches the value to the first pattern possible and execute the
 * callback by passing the arguments destructured from the value.
 *
 * @param mixed $value
 * @param array $patterns <pattern> => <callback>
 * @return mixed
 */
function match($value, array $patterns)
{
    $parser = new Parser();

    foreach($patterns as $pattern => $callback) {
        $match = $parser->parse($value, $pattern);

        if($match !== false) {
            return is_callable($callback) ?
                call_user_func_array($callback, $match) :
                $callback;
        }
    }

    throw new \RuntimeException('Non-exhaustive patterns.');
}

/**
 * Helper function to split a string using a given delimiter except
 * if said delimiter is enclosed between two different characters.
 *
 * This won't work if the opening and closing character for the
 * enclosure is the same (ie quotes), $open and $close need to
 * be different.
 *
 * The enclosing can have multiple depth. Each opening character needs
 * to be closed by exactly one closing character. No balancing is done.
 *
 * @param string $delimiter one character that will be the delimiter
 * @param string $open one character that starts the enclosing
 * @param string $close one character that stops the enclosing
 * @param string $string the string to split
 * @return array|string[] The split result.
 */
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

