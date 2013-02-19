<?php

namespace Carica\Io\Firmata {

  /**
   *
   * @property-read Carica\Io\Firmata\Board $board
   * @property-read integer $pin
   * @property-read array $supports
   * @property-read integer $value
   * @property integer $analog
   * @property boolean $digital
   */
  class Pin {

    /**
     * @var Carica\Io\Firmata\Board
     */
    private $_board = NULL;
    private $_pin = 0;
    private $_supports = array();
    private $_mode = PIN_STATE_OUTPUT;
    private $_value = 0;

    public function __construct(Board $board, $pin, array $supports) {
      $this->_board = $board;
      $that = $this;
      $this->_board->events()->on(
        'pin-state-'.$pin,
        function ($mode, $value) use ($that) {
          $that->onUpdatePinState($mode, $value);
        }
      );
      $this->_board->events()->on(
        'analog-read-'.$pin,
        function ($value) use ($that) {
          $that->onUpdateValue($value);
        }
      );
      $this->_board->events()->on(
        'digital-read-'.$pin,
        function ($value) use ($that) {
          $that->onUpdateValue($value);
        }
      );
      $this->_pin = (int)$pin;
      $this->_supports = $supports;
    }

    private function onUpdatePinState($mode, $value) {
      $this->_mode = $mode;
      $this->_value = $value;
    }

    private function onUpdateValue($value) {
      $this->_value = $value;
    }

    public function __isset($name) {
      switch ($name) {
      case 'board' :
      case 'pin' :
      case 'supports' :
      case 'mode' :
      case 'value' :
      case 'digital' :
      case 'analog' :
        return isset($this->{$name});
      }
      return FALSE;
    }

    public function __get($name) {
      switch ($name) {
      case 'board' :
        return $this->_board;
      case 'pin' :
        return $this->_pin;
      case 'supports' :
        return $this->_supports;
      case 'mode' :
        return $this->_mode;
      case 'value' :
        return $this->_value;
      case 'digital' :
        return ($this->_value == DIGITAL_HIGH);
      case 'analog' :
        return $this->_value;
      }
      throw new \LogicException(sprintf('Unknown property %s::$%s', get_class($this), $name));
    }

    public function __set($name, $value) {
      switch ($name) {
      case 'mode' :
        $this->setMode($value);
        return;
      case 'digital' :
        $this->setDigital($value);
        return;
      case 'analog' :
        $this->setAnalog($value);
        return;
      }
      throw new \LogicException(
        sprintf('Property %s::$%s can not be written', get_class($this), $name)
      );
    }

    public function setMode($mode) {
      $mode = (int)$mode;
      if (!in_array($mode, $this->_supports)) {
        throw new \OutOfBoundsException(
          sprintf('Invalid mode %d for pin #%d.', $mode, $this->_pin)
        );
      }
      if ($this->_mode != $mode) {
        $this->_mode = $mode;
        $this->_board->pinMode($this->_pin, $mode);
      }
    }

    public function setDigital($isActive) {
      $value = (boolean)$isActive ? DIGITAL_HIGH : DIGITAL_LOW;
      if ($this->_value != $value) {
        $this->_value = $value;
        $this->_board->digitalWrite($this->_pin, $value);
      }
    }

    public function setAnalog($value) {
      $value = (int)$value;
      if ($this->_value != $value) {
        $this->_value = $value;
        $this->_board->analogWrite($this->_pin, $value);
      }
    }
  }
}
