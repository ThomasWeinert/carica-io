<?php

namespace Carica\Io\Firmata\Response\Midi {

  use Carica\Io\Firmata;

  abstract class Message extends Firmata\Response\Midi {

    private $_pin = 0;
    private $_value = 0;

    public function __construct(array $bytes) {
      parent::__construct($bytes);
      $this->_pin = $bytes[0] & 0x0F;
      $this->_value = $bytes[1] | ($bytes[2] << 7);
    }

    public function __get($name) {
      switch ($name) {
      case 'pin' :
        return $this->_pin;
      case 'value' :
        return $this->_value;
      }
      throw new \LogicException(sprintf('Unknown property %s::$%s', __CLASS__, $name));
    }
  }
}