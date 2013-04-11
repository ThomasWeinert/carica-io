<?php

namespace Carica\Io\Network\Http {

  /**
   * An encaspulation to provide easier http header handling. Basically it allows to
   * treat an http header as an string or an array. If i is treated as an string the first
   * element in the internal list is used.
   *
   * This allows to handle http headers with mulitple values if need, without any syntactical
   * overhead for the most cases.
   *
   * @property string $name
   * @property string $value
   * @property \ArrayObject $values
   */
  class Header
    implements \ArrayAccess, \IteratorAggregate, \Countable {

    /**
     * The http header name
     * @var string
     */
    private $_name = '';
    /**
     * @var \ArrayObject
     */
    private $_values = NULL;

    /**
     * @param string $name
     * @param string|array|Traversable $data
     */
    public function __construct($name, $data = array()) {
      $this->setName($name);
      $this->setData($data);
    }

    /**
     * Set the http header name
     * @param string $name
     */
    public function setName($name) {
      if (trim($name) == '') {
        throw \UnexpectedValueException(
          sprintf('Property %s::$name can not be empty', __CLASS__)
        );
      }
      $this->_name = $name;
    }

    /**
     * Set the header data, can be a single value or an array
     *
     * @param string|array|Traversable
     */
    public function setData($data) {
      $this->_values = new \ArrayObject;
      if (is_array($data) || $data instanceOf Traversable) {
        foreach ($data as $value) {
          $this->_values[] = (string)$value;
        }
      } else {
        $this->values[] = (string)$data;
      }
    }

    /**
     * Casting the object to string will return the first value
     * @return string
     */
    public function __toString() {
      return (string)reset($this->_values);
    }

    /**
     * @param string $name
     * @return boolean
     */
    public function __isset($name) {
      switch ($name) {
      case 'name' :
      case 'value' :
      case 'values' :
        return TRUE;
      }
      return FALSE;
    }

    /**
     * @param string $name
     * @throws InvalidArgumentException
     * @return string|mixed|ArrayObject
     */
    public function __get($name) {
      switch ($name) {
      case 'name' :
        return $this->_name;
      case 'value' :
        return reset($this->_values);
      case 'values' :
        return $this->_values;
      }
      throw new \LogicException(
        sprintf('Can not read non existing property: %s::$%s', __CLASS__, $name)
      );
    }

    /**
     * @param string $name
     * @param string|array|Traversable $value
     * @throws \LogicException
     */
    public function __set($name, $value) {
      switch ($name) {
      case 'name' :
        $this->setName($value);
        return;
      case 'value' :
        $this->setData((string)$value);
        return;
      case 'values' :
        $this->setData($value);
        return;
      }
      throw new \LogicException(
        sprintf('Can not write non existing property: %s::$%s', __CLASS__, $name)
      );
    }

    /**
     * @param integer $offset
     * @return boolean
     */
    public function offsetExists($offset) {
      return $this->_values->offsetExists($offset);
    }

    /**
     * @param integer $offset
     */
    public function offsetGet($offset) {
      return $this->_values->offsetGet($offset);
    }

    /**
     * @param integer $offset
     * @param string $value
     */
    public function offsetSet($offset, $value) {
      $this->_values->offsetSet($offset, (string)$value);
    }

    /**
     * @param integer $offset
     */
    public function offsetUnset($offset) {
      $this->_values->offsetUnset($offset, (string)$value);
    }

    public function getIterator() {
      return $this->_values->getIterator();
    }

    public function count() {
      return count($this->_values);
    }
  }
}