<?php

namespace FunctionalPHP\PatternMatching;

class Parser
{
    protected $reserved = ['true', 'false'];

    protected $rules = [
        '/^(true|false)$/i' => '_parseBooleanConstant',
        '/^([\'"])(?:(?!\\1).)*\\1$/' => '_parseStringConstant',
        '/^[a-zA-Z]+$/' => '_parseIdentifier',
        '/^_$/' => '_parseWildcard',
        '/^\\[.*\\]$/' => '_parseArray',
        '/^\\(.+:.+\\)$/' => '_parseCons',
        '/^[a-zA-Z]+@.+$/' => '_parseAs',
    ];

    protected function _parseNumericConstant($value, $pattern)
    {
        return is_numeric($value) && $pattern == $value ? [] : false;
    }

    protected function _parseBooleanConstant($value, $pattern)
    {
        $pattern_value = strtoupper($pattern) === 'TRUE' ? true : false;
        return is_bool($value) && $value === $pattern_value ? [] : false;
    }

    protected function _parseStringConstant($value, $pattern)
    {
        $string_pattern = substr($pattern, 1, -1);
        return is_string($value) && $string_pattern == $value ? [] : false;
    }

    protected function _parseIdentifier($value, $pattern)
    {
        return in_array(strtolower($pattern), $this->reserved) ? false : [$pattern => $value];
    }

    protected function _parseWildcard()
    {
        return [];
    }

    protected function _parseArray($value, $pattern)
    {
        $patterns = $this->_split(',', '[', ']', substr($pattern, 1, -1));

        if(count($patterns) === 0) {
            return count($value) === 0 ? [] : false;
        }

        return $this->_recurse($value, $patterns);
    }

    protected function _parseCons($value, $pattern)
    {
        $patterns = $this->_split(':', '(', ')', substr($pattern, 1, -1));
        $last_pattern = array_pop($patterns);

        if(! is_array($value)) {
            return false;
        }

        return $this->_mergeResults(
            $this->parse($last_pattern, array_splice($value, count($patterns))),
            $this->_recurse($value, $patterns)
        );
    }

    protected function _parseAs($value, $pattern)
    {
        $patterns = explode('@', $pattern, 2);

        $rest = $this->parse($patterns[1], $value);
        return $this->_mergeResults($rest, [$patterns[0] => $value]);
    }

    /**
     * @param string $pattern
     * @param mixed $value
     * @return bool|array
     */
    public function parse($pattern, $value)
    {
        $pattern = trim($pattern);

        if(is_numeric($pattern)) {
            return $this->_parseNumericConstant($value, $pattern);
        }

        // a true value will mean that no regex matched
        // a false value will mean that at least one regex matched but the pattern didn't
        // anything else is the result of the pattern matching
        $result = array_reduce(array_keys($this->rules), function($current, $regex) use($value, $pattern) {
            return $this->_updateParsingResult($value, $pattern, $regex, $current);
        }, true);

        if($result === true) {
            $this->_invalidPattern($pattern);
        }

        return $result;
    }

    protected function _updateParsingResult($value, $pattern, $regex, $current)
    {
        if(is_bool($current) && preg_match($regex, $pattern)) {
            $current = call_user_func_array([$this, $this->rules[$regex]], [$value, $pattern]);
        }

        return $current;
    }

    protected function _split($delimiter, $start, $stop, $pattern)
    {
        $result = split_enclosed($delimiter, $start, $stop, $pattern);

        if($result === false) {
            $this->_invalidPattern($pattern);
        }

        return $result;
    }

    protected function _recurse($value, $patterns)
    {
        if(! is_array($value) || count($patterns) > count($value)) {
            return false;
        }

        return array_reduce($patterns, function($results, $p) use(&$value) {
            return $this->_mergeResults($this->parse($p, array_shift($value)), $results);
        }, []);
    }

    protected function _mergeResults($new, $current)
    {
        if($new === false || $current === false) {
            return false;
        }

        $common = array_intersect_key($current, $new);
        if(count($common) > 0) {
            throw new \RuntimeException(sprintf('Non unique identifiers: "%s".', implode(', ', array_keys($common))));
        }

        return array_merge($current, $new);
    }

    protected function _invalidPattern($pattern)
    {
        throw new \RuntimeException(sprintf('Invalid pattern "%s".', $pattern));
    }
}