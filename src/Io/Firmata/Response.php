<?php

namespace Carica\Io\Firmata {

  abstract class Response {

    private $_command = 0x00;
    private $_bytes = array();

    public function __construct(array $bytes) {
      $this->_command = array_shift($bytes);
      $this->_bytes = $bytes;
    }

    public function getCommand() {
      return $this->_command;
    }

    /**
     * Join groups of to 7 bit bytes into 8 bit bytes.
     *
     * @param string $data
     * @return string
     */
    public static function decodeBytes($data) {
      $bytes = array_slice(unpack("C*", "\0".$data), 1);
      $length = count($bytes);
      $result = '';
      for ($i = 0; $i < $length - 1; $i += 2) {
        $result .= pack('C', ($bytes[$i] & 0x7F) | (($bytes[$i + 1] & 0x7F) << 7));
      }
      return $result;
    }
  }
}
