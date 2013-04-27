<?php

namespace Carica\Io\Firmata\Exception {

  use Carica\Io;

  class UnsupportedMode extends \Exception implements Io\Exception {

    private $_modes = array(
      Io\Firmata\PIN_STATE_OUTPUT => 'digital output',
      Io\Firmata\PIN_STATE_INPUT => 'digital input',
      Io\Firmata\PIN_STATE_ANALOG => 'analog input',
      Io\Firmata\PIN_STATE_PWM => 'pwm output'
    );

    public function __construct($pin, $mode) {
      parent::__construct(
        sprintf('Pin %d does not support mode "%s"', $pin, $this->_modes[$mode])
      );
    }
  }
}