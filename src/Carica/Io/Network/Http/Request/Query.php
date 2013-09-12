<?php

namespace Carica\Io\Network\Http\Request {

  class Query implements \Countable, \ArrayAccess, \IteratorAggregate {

    private $_data = array();

    public function __construct($string = '') {
      $this->setQueryString($string);
    }

    public function setQueryString($string) {
      $attributes = explode('&', $string);
      foreach ($attributes as $attribute) {
        if (empty($attribute)) {
          continue;
        } elseif (FALSE === strpos($attribute, '=')) {
          $this->_data[urldecode($attribute)] = TRUE;
        } else {
          list($name, $value) = explode('=', $attribute);
          $this->_data[urldecode($name)] = urldecode($value);
        }
      }
    }

    public function count() {
      return count($this->_data);
    }

    public function getIterator() {
      return new \ArrayIterator($this->_data);
    }

    public function offsetExists($name) {
      return array_key_exists($name, $this->_data);
    }

    public function offsetGet($name) {
      return $this->_data[$name];
    }

    public function offsetSet($name, $value) {
      if (empty($name)) {
        throw new \InvalidArgumentException('Query string parameter name can not be empty.');
      }
      $this->_data[$name] = $value;
    }

    public function offsetUnset($name) {
      unset($this->_data[$name]);
    }
  }
}
