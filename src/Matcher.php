<?php

namespace FunctionalPHP\PatternMatching;

class Matcher
{
    protected static function parser() {
        static $parser = null;

        if(is_null($parser)) {
            $parser = new Parser();
        }

        return $parser;
    }

    /**
     * @param mixed $value
     * @param array $patterns
     * @return mixed
     */
    public static function match($value, array $patterns)
    {
        foreach($patterns as $pattern => $callback) {
            $match = static::parser()->parse($value, $pattern);

            if($match !== false) {
                return is_callable($callback) ?
                    call_user_func_array($callback, $match) :
                    $callback;
            }
        }

        throw new \RuntimeException('Non-exhaustive patterns.');
    }
}