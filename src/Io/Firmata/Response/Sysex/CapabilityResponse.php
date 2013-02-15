<?php

namespace Carica\Io\Firmata\Response\Sysex {

  use Carica\Io\Firmata;

  class CapabilityResponse extends Firmata\Response\Sysex {

    private $_supported = array(
      Firmata\PIN_STATE_INPUT,
      Firmata\PIN_STATE_OUTPUT,
      Firmata\PIN_STATE_ANALOG,
      Firmata\PIN_STATE_PWM,
      Firmata\PIN_STATE_SERVO
    );

    private $_pins = array();

    public function __construct($bytes) {
      parent::__construct($bytes);
      $length = count($bytes);
      $supported = 0;
      $byteIndex = 0;
      for ($i = 1; $i < $length; ++$i) {
        if ($bytes[$i] == 127) {
          $modes = array();
          foreach ($this->_supported as $mode) {
            if ($supported & (1 << $mode)) {
              $modes[] = $mode;
            }
          }
          $this->_pins[] = array(
            'supports' => $modes,
            'mode' => Firmata\PIN_STATE_OUTPUT,
            'value' => 0
          );
          $supported = 0;
          $byteIndex = 0;
          continue;
        }
        if ($byteIndex === 0) {
          $supported |= (1 << $bytes[$i]);
        }
        $byteIndex ^= $byteIndex;
      }
    }

    public function __get($name) {
      switch ($name) {
      case 'pins' :
        return $this->_pins;
      }
      throw new \LogicException(sprintf('Unknown property %s::$%s', __CLASS__, $name));
    }
  }
}