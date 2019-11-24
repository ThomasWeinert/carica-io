<?php
declare(strict_types=1);

namespace Carica\Io\Network\HTTP {

  use ArrayAccess;
  use ArrayIterator;
  use Countable;
  use InvalidArgumentException;
  use Iterator;
  use IteratorAggregate;

  class Headers implements IteratorAggregate, Countable, ArrayAccess {

    private $_headers = array();

    public function count(): int {
      return count($this->_headers);
    }

    public function getIterator(): Iterator {
      return new ArrayIterator($this->_headers);
    }

    public function offsetExists($name) {
      return array_key_exists($this->prepareKey($name), $this->_headers);
    }

    public function offsetGet($name) {
      return $this->_headers[$this->prepareKey($name)];
    }

    public function offsetSet($name, $value) {
      if (
        (!$value instanceOf Header) &&
        FALSE !== strpos((string)$value, ':')) {
        [$name, $value] = explode(':', $value, 2);
      }
      if ($value instanceOf Header) {
        $name = $value->name;
      }
      $key = $this->prepareKey($name);
      if (isset($this->_headers[$key])) {
        $this->_headers[$key]->values[] = $value;
      } elseif ($value instanceOf Header) {
        $this->_headers[$key] = $value;
      } else {
        $this->_headers[$key] = new Header($name, $value);
      }
    }

    public function offsetUnset($name) {
      unset($this->_headers[$this->prepareKey($name)]);
    }

    private function prepareKey(string $name): string {
      $name = trim($name);
      if (empty($name)) {
        throw new InvalidArgumentException('The header name can not be empty.');
      }
      if (!preg_match('(^[a-z][a-z\d]*(?:-[a-z\d]+)*$)iD', $name)) {
        throw new InvalidArgumentException(
          sprintf(
            'The header name "%s" is invalid.', $name
          )
        );
      }
      return strToLower($name);
    }
  }
}
