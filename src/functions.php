<?php

namespace FunctionalPHP\PatternMatching;

/**
 * Destructure the given value using the given pattern, then returns
 * the resulting values as an array indexed using the identifiers of
 * the pattern.
 *
 * If the extraction failed, will return False.
 *
 * @param string $pattern
 * @param mixed $value
 * @return array|bool|callable
 */
function extract($pattern, $value = null)
{
    $function = function($value) use($pattern) {
        return (new Parser())->parse($pattern, $value);
    };

    return func_num_args() > 1 ? $function($value) : $function;
}

/**
 * Given a value and an array with the format <pattern> => <callback>,
 * matches the value to the first pattern possible and execute the
 * callback by passing the arguments destructured from the value.
 *
 * @param array $patterns <pattern> => <callback>
 * @param mixed $value
 * @return array|mixed|callable
 */
function match(array $patterns, $value = null)
{
    $function = function($value) use($patterns) {
        $parser = new Parser();

        foreach($patterns as $pattern => $callback) {
            $match = $parser->parse($pattern, $value);

            if($match !== false) {
                return is_callable($callback) ?
                    call_user_func_array($callback, $match) :
                    $callback;
            }
        }

        throw new \RuntimeException('Non-exhaustive patterns.');
    };

    return func_num_args() > 1 ? $function($value) : $function;
}

/**
 * Helper to create a function for which the value depends on pattern matching.
 *
 * You pass the various parameters your function except separated
 * by spaces, the helper will automatically transform this to accept
 * an array as parameter by replacing all spaces with commas.
 *
 * This means you cannot use any space in your patterns.
 *
 * @param array $patterns
 * @return \Closure|callable
 */
function func(array $patterns)
{
    $array_patterns = array_combine(array_map(function($k) {
        return '['.implode(', ', explode(' ', $k)).']';
    }, array_keys($patterns)), array_values($patterns));

    return function() use($array_patterns) {
        return match($array_patterns, func_get_args());
    };
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

    $results = [];
    $buffer = '';
    $depth = 0;
    foreach(str_split($string) as $c) {
        if($c === ' ') {
            continue;
        }

        if($c === $delimiter && $depth === 0) {
            if(strlen($buffer) === 0) {
                return false;
            }

            $results[] = $buffer;
            $buffer = '';
            continue;
        }

        if($c === $open) {
            ++$depth;
        } else if($c === $close) {
            --$depth;
        }

        $buffer .= $c;
    }

    return strlen($buffer) === 0 ? false : array_merge($results, [$buffer]);
}

