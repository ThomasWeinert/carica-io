<?php

namespace Carica\Io\Firmata {

  abstract class Request {

    /**
     * Split a string with 8 bit bytes into 2 7bit bytes.
     *
     * @param string $data
     * @return string
     */
    public static function encodeBytes($data) {
      $bytes = array_slice(unpack("C*", "\0".$data), 1);
      $result = '';
      foreach ($bytes as $byte) {
        $result .= pack('CC', $byte & 0x7F, ($byte >> 7) & 0x7F);
      }
      return $result;
    }
  }
}
