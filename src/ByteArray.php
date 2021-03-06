<?php
declare(strict_types=1);

namespace Carica\Io {

  use ArrayAccess;
  use ArrayIterator;
  use Countable;
  use Iterator;
  use IteratorAggregate;
  use OutOfBoundsException;
  use OutOfRangeException;

  /**
   * Flexible access to an array of bytes, allow bit handling
   */
  class ByteArray implements ArrayAccess, IteratorAggregate, Countable {

    public const BIT_ONE = 1;
    public const BIT_TWO = 2;
    public const BIT_THREE = 4;
    public const BIT_FOUR = 8;
    public const BIT_FIVE = 16;
    public const BIT_SIX = 32;
    public const BIT_SEVEN = 64;
    public const BIT_EIGHT = 128;

    private $_bytes = array();
    private $_length = 0;

    /**
     * Create array of bytes and set length.
     *
     * @param int $length
     * @throws OutOfRangeException
     */
    public function __construct(int $length = 1) {
      $this->setLength($length);
    }

    /**
     * Resize the byte array, additional bytes will be filled with zero.
     *
     * @param integer $length
     * @throws OutOfRangeException
     */
    public function setLength(int $length) {
      if ((int)$length < 1) {
        throw new OutOfRangeException('Zero or negative length is not possible');
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
     * Return the current byte length
     *
     * @return int
     */
    public function getLength(): int {
      return $this->_length;
    }

    /**
     * Get the byte array as an binary string.
     *
     * @return string
     */
    public function __toString() {
      return (string)pack(...array_merge(array('C*'), $this->_bytes));
    }

    /**
     * read the bytes from an binary string
     *
     * @param string $string
     * @param boolean $resize to binary string length
     * @return $this
     * @throws OutOfRangeException
     * @throws OutOfBoundsException
     */
    public function fromString(string $string, bool $resize = FALSE): self {
      if ($resize && strlen($string) !== $this->getLength()) {
        $this->setLength(strlen($string));
      }
      $bytes = array_slice(unpack('C*', "\0".$string), 1);
      if (count($bytes) >= $this->_length) {
        for ($i = 0; $i < $this->_length; ++$i) {
          $this->_bytes[$i] = $bytes[$i];
        }
      } else {
        throw new OutOfBoundsException(
          sprintf(
            'Maximum length is "%d". Got "%d".', $this->_length, count($bytes)
          )
        );
      }
      return $this;
    }

    /**
     * Read an hexadecimal encoded binary string
     *
     * @param string $string
     * @param boolean $resize
     * @return $this
     * @throws OutOfRangeException
     * @throws OutOfBoundsException
     */
    public function fromHexString(string $string, bool $resize = FALSE): self {
      $string = str_replace(' ', '', $string);
      $length = (int)floor(strlen($string) / 2);
      if ($resize && $length !== $this->getLength()) {
        $this->setLength($length);
      }
      if ($length >= $this->_length) {
        for ($i = 0; $i < $this->_length; ++$i) {
          $this->_bytes[$i] = hexdec(substr($string, $i * 2, 2));
        }
      } else {
        throw new OutOfBoundsException(
          sprintf(
            'Maximum length is "%d". Got "%d".', $this->_length, $length
          )
        );
      }
      return $this;
    }

    /**
     * Read an hexadecimal encoded binary string
     *
     * @param array $bytes
     * @param boolean $resize
     * @return $this
     * @throws OutOfRangeException
     * @throws OutOfBoundsException
     */
    public function fromArray(array $bytes, bool $resize = FALSE): self {
      $length = count($bytes);
      if ($resize && $length !== $this->getLength()) {
        $this->setLength($length);
      }
      if ($length >= $this->_length) {
        foreach (array_values($bytes) as $index => $byte) {
          $this->_bytes[$index] = $byte;
        }
      } else {
        throw new OutOfBoundsException(
          sprintf(
            'Maximum length is "%d". Got "%d".', $this->_length, $length
          )
        );
      }
      return $this;
    }

    /**
     * Get the byte array as an hexadecimal encoded string.
     *
     * @param string $separator
     * @return string
     */
    public function asHex(string $separator = ''): string {
      $result = '';
      foreach ($this->_bytes as $byte) {
        $result .= str_pad(dechex($byte), 2, '0', STR_PAD_LEFT).$separator;
      }
      return empty($separator)
        ? $result
        : substr($result, 0, -strlen($separator));
    }

    /**
     * Get the bytes as bit string separated by spaces.
     *
     * @return string
     */
    public function asBitString(): string {
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
    public function asArray(): array {
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
     * @param int|array(int,int) $offset
     * @return bool
     */
    public function offsetExists($offset): bool {
      try {
        $this->validateOffset($offset);
      } catch (OutOfBoundsException $e) {
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
     * @throws OutOfBoundsException
     */
    public function offsetGet($offset) {
      $this->validateOffset($offset);
      if (is_array($offset)) {
        $bit = ($offset[1] > 0) ? 1 << $offset[1] : 1;
        return ($this->_bytes[$offset[0]] & $bit) === $bit;
      }
      return $this->_bytes[$offset];
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
     * @param integer|integer[] $offset
     * @param integer|boolean $value
     * @throws OutOfRangeException
     * @throws OutOfBoundsException
     */
    public function offsetSet($offset, $value) {
      $this->validateOffset($offset);
      if (is_array($offset)) {
        $bit = ($offset[1] > 0) ? 1 << $offset[1] : 1;
        if ($value) {
          $this->_bytes[$offset[0]] |= $bit;
        } else {
          $this->_bytes[$offset[0]] &= ~$bit;
        }
      } else {
        $this->_bytes[$offset] = $this->validateValue((int)$value);
      }
    }

    /**
     * Reset an byte or bit
     *
     * If the index specifies a byte it is set to zero. If it specifies
     * and bit it is disabled.
     *
     * @see ArrayAccess::offsetUnset()
     * @param integer|array $offset
     * @throws OutOfRangeException
     * @throws OutOfBoundsException
     */
    public function offsetUnset($offset) {
      $this->offsetSet($offset, is_array($offset) ? FALSE : 0);
    }

    /**
     * Validate the given offset is usable as byte or bit offset
     *
     * @param integer|array(integer,integer) $offset
     * @throws OutOfBoundsException
     */
    private function validateOffset($offset): void {
      if (is_array($offset)) {
        $this->validateBitOffset($offset);
      } elseif ($offset < 0 || $offset >= $this->_length) {
        throw new OutOfBoundsException(
          sprintf(
            'Maximum byte index is "%d". Got "%d".', $this->_length - 1, $offset
          )
        );
      }
    }

    /**
     * Validate the given offset is usable as bit offset
     *
     * @param int[] $offset
     * @throws OutOfBoundsException
     */
    private function validateBitOffset(array $offset): void {
      if (count($offset) !== 2) {
        throw new OutOfBoundsException(
          sprintf(
            'Bit index needs two elements (byte and bit index). Got "%d".', count($offset)
          )
        );
      }
      $this->validateOffset($offset[0]);
      if ($offset[1] < 0 || $offset[1] >= 8) {
        throw new OutOfBoundsException(
          sprintf(
            'Maximum bit index is "7". Got "%d".', $offset[1]
          )
        );
      }
    }

    /**
     * Validate the given value is valid for a single byte
     *
     * @throws OutOfRangeException
     * @param int $value
     * @return int
     */
    private function validateValue(int $value): int {
      if ($value < 0 || $value > 255) {
        throw new OutOfRangeException(
          sprintf('Byte value expected (0-255). Got "%d"', $value)
        );
      }
      return $value;
    }

    /**
     * Allow to iterate the bytes
     * @return Iterator
     */
    public function getIterator(): Iterator {
      return new ArrayIterator($this->_bytes);
    }

    /**
     * Return byte count
     *
     * @return int
     */
    public function count(): int {
      return count($this->_bytes);
    }

    /**
     * Create an ByteArray from an hexadecimal byte string
     * @param string $hexString
     * @return self
     * @throws OutOfRangeException
     * @throws OutOfBoundsException
     */
    public static function createFromHex(string $hexString): self {
      $bytes = new self();
      $bytes->fromHexString($hexString, TRUE);
      return $bytes;
    }

    /**
     * Create a new ByteArray from an array of bytes
     *
     * @param array $array
     * @return self
     * @throws OutOfBoundsException
     * @throws OutOfRangeException
     */
    public static function createFromArray(array $array): self {
      $bytes = new self();
      $bytes->fromArray($array, TRUE);
      return $bytes;
    }
  }
}
