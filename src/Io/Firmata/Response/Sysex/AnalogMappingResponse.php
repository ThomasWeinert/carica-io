<?php

namespace Carica\Io\Firmata\Response\Sysex {

  use Carica\Io\Firmata;

  class AnalogMappingResponse extends Firmata\Response\Sysex {

    private $_channels = array();

    public function __construct($bytes) {
      parent::__construct($bytes);
      $length = count($bytes);
      for ($i = 1, $pin = 0; $i < $length; ++$i, ++$pin) {
        $current = $bytes[$i];
        $this->_channels[$pin] = ($current == 127) ? NULL : $current;
      }
    }

    public function __get($name) {
      switch ($name) {
      case 'channels' :
        return $this->_channels;
      }
      throw new \LogicException(sprintf('Unknown property %s::$%s', __CLASS__, $name));
    }
  }
}