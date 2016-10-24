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
 * The given string will be trimmed as will all values in the
 * resulting array. An empty string will result in an empty
 * array. If at any time a string that should be added to the
 * result is empty, the function will return false instead.
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
 * @return array|string[]|bool The split result, false if any of the value was empty
 */
function split_enclosed($delimiter, $open, $close, $string)
{
    $string = trim($string);
    if(strlen($string) === 0) {
        return [];
    }

    $chars = str_split($string);

    $result = array_reduce($chars, function($acc, $c) use($delimiter, $open, $close) {
        if($acc === false) {
            return $acc;
        }

        switch($c) {
            case $delimiter:
                if($acc[2] === 0) {
                    return strlen(trim($acc[1])) === 0 ?
                        false :
                        [array_merge($acc[0], [trim($acc[1])]), '', 0];
                }
                break;
            case $open:
                return [$acc[0], $acc[1].$c, $acc[2] + 1];
            case $close:
                return [$acc[0], $acc[1].$c, $acc[2] - 1];
        }

        return [$acc[0], $acc[1].$c, $acc[2]];
    }, [[], '', 0]);

    if($result === false || strlen(trim($result[1])) === 0) {
        return false;
    }

    return array_merge($result[0], [trim($result[1])]);
}

