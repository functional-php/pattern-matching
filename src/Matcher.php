<?php

namespace PHPFunctional\PatternMatching;

class Matcher
{
    /**
     * @param mixed $value
     * @param string $pattern
     * @return bool|array
     */
    private static function parse($value, $pattern)
    {
        return false;
    }

    /**
     * @param mixed $value
     * @param array $patterns
     * @return mixed
     */
    public static function match($value, array $patterns)
    {
        foreach($patterns as $pattern => $callback) {
            $match = self::parse($value, $pattern);

            if($match !== false) {
                return call_user_func_array($callback, $match);
            }
        }

        throw new \RuntimeException("Non-exhaustive patterns.");
    }
}