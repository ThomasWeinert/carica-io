<?php

namespace Carica\Io {

  /**
   * Flexible access to an array of bytes, allow bit handling
   */
  class ByteArray implements \ArrayAccess, \IteratorAggregate, \Countable {

    const BIT_ONE = 1;
    const BIT_TWO = 2;
    const BIT_THREE = 4;
    const BIT_FOUR = 8;
    const BIT_FIVE = 16;
    const BIT_SIX = 32;
    const BIT_SEVEN = 64;
    const BIT_EIGHT = 128;

    private $_bytes = array();
    private $_length = 0;

    /**
     * Create array of bytes and set length.
     *
     * @param integer $length
     * @throws \OutOfRangeException
     */
    public function __construct($length = 1) {
      $this->setLength($length);
    }

    /**
     * Resize the byte array, additional bytes will be filled with zero.
     *
     * @param integer $length
     * @throws \OutOfRangeException
     */
    public function setLength($length) {
      if ((int)$length < 1) {
        throw new \OutOfRangeException('Zero or negative length is not possible');
      }
      $difference = $length - $this->_length;
      if ($difference > 0) {
        $this->_bytes = array_merge($this->_bytes, array_fill($this->_length, $difference, 0));
      } elseif ($difference < 0) {
        $this->_bytes = array_slice($this->_bytes, 0, $difference);
      }
      $this->_length = (int)$length;
    }

    /**
     * Return teh current byte length
     *
     * @return integer
     */
    public function getLength() {
      return $this->_length;
    }

    /**
     * Get the byte array as an binary string.
     *
     * @return string
     */
    public function __toString() {
      return call_user_func_array('pack', array_merge(array("C*"), $this->_bytes));
    }

    /**
     * read the bytes from an binary string
     *
     * @param string $string
     * @param boolean $resize to binary string length
     * @throws \OutOfBoundsException
     */
    public function fromString($string, $resize = FALSE) {
      if ($resize && strlen($string) != $this->getLength()) {
        $this->setLength(strlen($string));
      }
      $bytes = array_slice(unpack("C*", "\0".$string), 1);
      if (count($bytes) >= $this->_length) {
        for ($i = 0; $i < $this->_length; ++$i) {
          $this->_bytes[$i] = $bytes[$i];
        }
      } else {
        throw new \OutOfBoundsException(
          sprintf(
            'Maximum length is "%d". Got "%d".', $this->_length, count($bytes)
          )
        );
      }
    }

    /**
     * Read an hexadecimal encoded binary string
     *
     * @param string $string
     * @param boolean $resize
     * @throws \OutOfBoundsException
     */
    public function fromHexString($string, $resize = FALSE) {
      $string = str_replace(' ', '', $string);
      $length = floor(strlen($string) / 2);
      if ($resize && $length != $this->getLength()) {
        $this->setLength($length);
      }
      if ($length >= $this->_length) {
        for ($i = 0; $i < $this->_length; ++$i) {
          $this->_bytes[$i] = hexdec(substr($string, $i * 2, 2));
        }
      } else {
        throw new \OutOfBoundsException(
          sprintf(
            'Maximum length is "%d". Got "%d".', $this->_length, $length
          )
        );
      }
    }

    /**
     * Read an hexadecimal encoded binary string
     *
     * @param string $string
     * @param boolean $resize
     * @throws \OutOfBoundsException
     */
    public function fromArray(array $bytes, $resize = FALSE) {
      $length = count($bytes);
      if ($resize && $length != $this->getLength()) {
        $this->setLength($length);
      }
      if ($length >= $this->_length) {
        foreach (array_values($bytes) as $index => $byte) {
          $this->_bytes[$index] = $byte;
        }
      } else {
        throw new \OutOfBoundsException(
          sprintf(
            'Maximum length is "%d". Got "%d".', $this->_length, $length
          )
        );
      }
    }

    /**
     * Get the byte array as an hexdec string.
     *
     * @return string
     */
    public function asHex() {
      $result = '';
      foreach ($this->_bytes as $byte) {
        $result .= str_pad(dechex($byte), 2, '0', STR_PAD_LEFT);
      }
      return $result;
    }

    /**
     * Get the bytes as bit string seperated by spaces.
     *
     * @return string
     */
    public function asBitString() {
      $result = '';
      foreach ($this->_bytes as $byte) {
        $result .= ' '.str_pad(decbin($byte), 8, '0', STR_PAD_LEFT);
      }
      return substr($result, 1);
    }

    /**
     * Get the bytes as array of bytes, only needed for array functions.
     *
     * @return array
     */
    public function asArray() {
      return $this->_bytes;
    }

    /**
     * Check if the specified byte or bit offset exists.
     *
     * If the $offset is an array the first element is the byte index and the second the bin index.
     *
     * If the $offset is an integer it is the byte offset.
     *
     * @see ArrayAccess::offsetExists()
     * @param integer|array(integer,integer) $offset
     * @return integer|boolean
     */
    public function offsetExists($offset) {
      try {
        $this->validateOffset($offset);
      } catch (\OutOfBoundsException $e) {
        return FALSE;
      }
      return TRUE;
    }

    /**
     * Read if the specified byte or bit offset exists.
     *
     * If the $offset is an array the first element is the byte index and the second the bin index.
     * The result in this case is boolean.
     *
     * If the $offset is an integer it is the byte offset and the result is the byte values as
     * integer.
     *
     * Examples:
     *   $byte = $bytes[0]; // read the first byte
     *   $bit = $bytes[[0, 7]]; // read the highest bit of the first byte
     *
     * @see ArrayAccess::offsetGet()
     * @param integer|array(integer,integer) $offset
     * @return integer|boolean
     */
    public function offsetGet($offset) {
      $this->validateOffset($offset);
      if (is_array($offset)) {
        $bit = ($offset[1] > 0) ? 1 << $offset[1] : 1;
        return ($this->_bytes[$offset[0]] & $bit) == $bit;
      } else {
        return $this->_bytes[$offset];
      }
    }

    /**
     * Write if the specified byte or bit offset exists.
     *
     * If the $offset is an array the first element is the byte index and the second the bin index.
     * The value has to be boolean in this case.
     *
     * If the $offset is an integer it is the byte offset and the value is the byte value as
     * integer.
     *
     * Examples:
     *   $bytes[0] = 42; // write the first byte
     *   $bytes[[0, 7]] = TRUE; // set the highest bit of the first byte
     *
     * @see ArrayAccess::offsetSet()
     * @param integer|array(integer,integer) $offset
     * @param integer|boolean $value
     */
    public function offsetSet($offset, $value) {
      $this->validateOffset($offset);
      if (is_array($offset)) {
        $bit = ($offset[1] > 0) ? 1 << $offset[1] : 1;
        if ($value) {
          $this->_bytes[$offset[0]] = $this->_bytes[$offset[0]] | $bit;
        } else {
          $this->_bytes[$offset[0]] = $this->_bytes[$offset[0]] & ~$bit;
        }
      } else {
        $this->validateValue($value);
        $this->_bytes[$offset] = $value;
      }
    }

    /**
     * Reset an byte or bit
     *
     * If the index spezifies a byte it is set to zero. If it spdifies and bit it is disabled.
     *
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset) {
      $this->offsetSet($offset, is_array($offset) ? FALSE : 0);
    }

    /**
     * Validate the given offset is usable as byte or bit offset
     *
     * @param integer|array(integer,integer) $offset
     * @throws \OutOfBoundsException
     */
    private function validateOffset($offset) {
      if (is_array($offset)) {
        $this->validateBitOffset($offset);
      } else {
        if ($offset < 0 || $offset >= $this->_length) {
          throw new \OutOfBoundsException(
            sprintf(
              'Maximum byte index is "%d". Got "%d".', $this->_length - 1, $offset
            )
          );
        }
      }
    }

    /**
     * Validate the given offset is usable as bit offset
     *
     * @param array(integer,integer) $offset
     * @throws \OutOfBoundsException
     */
    private function validateBitOffset(array $offset) {
      if (count($offset) != 2) {
        throw new \OutOfBoundsException(
          sprintf(
            'Bit index needs two elements (byte and bit index). Got "%d".', count($offset)
          )
        );
      }
      $this->validateOffset($offset[0]);
      if ($offset[1] < 0 || $offset[1] >= 8) {
        throw new \OutOfBoundsException(
          sprintf(
            'Maximum bit index is "7". Got "%d".', $offset[1]
          )
        );
      }
    }

    /**
     * Validate the given value is a storeable in a single byte
     *
     * @param integer $offset
     * @throws \OutOfBoundsException
     */
    private function validateValue($value) {
      if (($value < 0 || $value > 255)) {
        throw new \OutOfRangeException(
          sprintf('Byte value expected (0-255). Got "%d"', $value)
        );
      }
    }

    /**
     * Allow to iterate the bytes
     * @return \Iterator
     */
    public function getIterator() {
      return new \ArrayIterator($this->_bytes);
    }

    /**
     * Return byte count
     *
     * @return Integer
     */
    public function count() {
      return count($this->_bytes);
    }

    /**
     * Create an ByteArray from an hexadecimal byte string
     * @param string $hexString
     * @return \Carica\Io\ByteArray
     */
    public static function createFromHex($hexString) {
      $bytes = new ByteArray();
      $bytes->fromHexString($hexString, TRUE);
      return $bytes;
    }

    /**
     * Create a new ByteArray from an array of bytes
     *
     * @param array $bytes
     * @return \Carica\Io\ByteArray
     */
    public static function createFromArray(array $array) {
      $bytes = new ByteArray();
      $bytes->fromArray($array, TRUE);
      return $bytes;
    }
  }
}
