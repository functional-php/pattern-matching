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

        $results = $this->_recurse($value, $patterns);

        if($results !== false) {
            $last_result = $this->parse(array_splice($value, count($patterns)), $last_pattern);

            $results = $last_result === false ? false : array_merge($results, $last_result);
        }

        return $results;
    }

    protected function _parseAs($value, $pattern)
    {
        $patterns = explode('@', $pattern, 2);

        $rest = $this->parse($value, $patterns[1]);
        return $rest === false ? false : array_merge([$patterns[0] => $value], $rest);
    }

    /**
     * @param mixed $value
     * @param string $pattern
     * @return bool|array
     */
    public function parse($value, $pattern)
    {
        $pattern = trim($pattern);

        if(is_numeric($pattern)) {
            return is_numeric($value) && $pattern == $value ? [] : false;
        }

        $matched = false;
        foreach($this->rules as $regex => $method) {
            if(preg_match($regex, $pattern)) {
                $matched = true;

                $arguments = call_user_func_array([$this, $method], [$value, $pattern]);

                if($arguments !== false) {
                    return $arguments;
                }
            }
        }

        if(! $matched) {
            $this->_invalidPattern($pattern);
        }

        return false;
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

        $results = [];
        foreach($patterns as $p) {
            $new = $this->parse(array_shift($value), $p);

            if($new === false) {
                return false;
            }

            $results = array_merge($results, $new);
        }

        return $results;
    }

    protected function _invalidPattern($pattern)
    {
        throw new \RuntimeException(sprintf('Invalid pattern "%s".', $pattern));
    }
}