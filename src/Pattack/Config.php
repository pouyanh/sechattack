<?php
/**
 * Created by IntelliJ IDEA.
 * User: pouyan
 * Date: 8/5/14
 * Time: 10:58 PM
 */

namespace Pattack;


class Config extends \Phalcon\Config
{
    protected $delimiter = '.';
    protected $variables = [];

    public function setDelimiter($delimiter)
    {
        if (!is_string($delimiter)) {
            throw new Exception('Expected `string` for delimiter');
        }

        $this->delimiter = $delimiter;
    }

    public function getDelimiter()
    {
        return $this->delimiter;
    }

    public function setVariable($name, $value)
    {
        if (!is_string($name)) {
            throw new Exception('Variable Name Should Be String');
        }

        if (!is_scalar($value)) {
            throw new Exception('Variable Value Should Be Scalar');
        }

        $this->variables[$name] = $value;
    }

    public function getVariable($name)
    {
        if (isset($this->variables[$name])) {
            return $this->variables[$name];
        } else {
            throw new Exception(sprintf('Illegal Offset %s', $name));
        }
    }

    protected function parse($value)
    {
        if (is_array($value) || $value instanceof \Phalcon\Config) {
            foreach ($value as $key => $item) {
                $value[$key] = $this->parse($item);
            }
        } elseif (is_string($value)) {
            foreach ($this->variables as $varName => $varValue) {
                $value = str_replace('%' . $varName . '%', $varValue, $value);
            }
        }

        return $value;
    }

    public function get($index, $defaultValue = null)
    {
        $keys = explode($this->delimiter, $index);
        $current = parent::get(array_shift($keys));
        while ($current instanceof \Phalcon\Config && count($keys) > 0) {
            $key = array_shift($keys);
            $default = (0 == count($keys)) ? $defaultValue : null;
            $current = $current->get($key, $default);
        }

        return $this->parse($current);
    }
}
