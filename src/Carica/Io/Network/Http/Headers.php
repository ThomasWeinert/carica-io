<?php

namespace Carica\Io\Network\Http {

  use Carica\Io;

  class Headers implements \IteratorAggregate, \Countable, \ArrayAccess {

    private $_headers = array();


    public function count() {
      return count($this->_headers);
    }

    public function getIterator() {
      return new \ArrayIterator($this->_headers);
    }

    public function offsetExists($name) {
      return array_key_exists($this->prepareKey($name, TRUE), $this->_headers);
    }

    public function offsetGet($name) {
      return $this->_headers[$this->prepareKey($name)];
    }

    public function offsetSet($name, $value) {
      if (!$value instanceOf Header) {
        if (FALSE != strpos($value, ':')) {
          list($name, $data) = explode(':', $value, 2);
          $value = new Header($name, $data);
        } else {
          $value = new Header($name, $value);
        }
      }
      if (NULL === $name) {
        $name = $value->name;
      }
      $this->_headers[$this->prepareKey($name)] = $value;
    }

    public function offsetUnset($name) {
      unset($this->_headers[$this->prepareKey($name)]);
    }

    private function prepareKey($name, $silent = FALSE) {
      $name = trim($name);
      if (empty($name)) {
        throw new \InvalidArgumentException('The header name can not be empty.');
      }
      if (!preg_match('(^[a-z][a-z\d]*(?:-[a-z\d]+)?$)iD', $name)) {
        throw new \InvalidArgumentException(
          sprintf(
            'The header name "%s" is invalid.', $name
          )
        );
      }
      return strToLower($name);
    }
  }
}