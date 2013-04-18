<?php

namespace Carica\Io\Firmata\Response\Sysex {

  use Carica\Io\Firmata;

  class QueryFirmware extends Firmata\Response\Sysex {

    private $_text = '';

    public function __construct($command, array $bytes) {
      parent::__construct($command, $bytes);
      $this->_text = self::decodeBytes(array_slice($bytes, 1));
    }

    public function __get($name) {
      switch ($name) {
      case 'text' :
        return $this->_text;
      }
      parent::__get($name);
    }
  }
}