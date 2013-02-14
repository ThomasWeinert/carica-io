<?php

namespace Carica\Io\Firmata {

  use Carica\Io\Event;
  use Carica\Io;

  const COMMAND_PIN_MODE = 0xF4;
  const COMMAND_REPORT_DIGITAL = 0xD0;
  const COMMAND_REPORT_ANALOG = 0xC0;
  const COMMAND_DIGITAL_MESSAGE = 0x90;
  const COMMAND_START_SYSEX = 0xF0;
  const COMMAND_END_SYSEX = 0xF7;
  const COMMAND_QUERY_FIRMWARE = 0x79;
  const COMMAND_REPORT_VERSION = 0xF9;
  const COMMAND_ANALOG_MESSAGE = 0xE0;
  const COMMAND_CAPABILITY_QUERY = 0x6B;
  const COMMAND_CAPABILITY_RESPONSE = 0x6C;
  const COMMAND_PIN_STATE_QUERY = 0x6D;
  const COMMAND_PIN_STATE_RESPONSE = 0x6E;
  const COMMAND_ANALOG_MAPPING_QUERY = 0x69;
  const COMMAND_ANALOG_MAPPING_RESPONSE = 0x6A;
  const COMMAND_I2C_REQUEST = 0x76;
  const COMMAND_I2C_REPLY = 0x77;
  const COMMAND_I2C_CONFIG = 0x78;
  const COMMAND_STRING_DATA = 0x71;
  const COMMAND_SYSTEM_RESET = 0xFF;

  const PIN_STATE_INPUT = 0x00;
  const PIN_STATE_OUTPUT = 0x01;
  const PIN_STATE_ANALOG = 0x02;
  const PIN_STATE_PWM = 0x03;
  const PIN_STATE_SERVO = 0x04;

  const DIGITAL_LOW = 0;
  const DIGITAL_HIGH = 1;

  class Board {

    use Event\Emitter\Aggregation;

    private $_pins = array();
    private $_serialPort = NULL;

    private $_buffer = NULL;

    private $_version = array(
      'major' => 0,
      'minor' => 0
    );

    private $_activationCallback = FALSE;

    public function __construct(Io\Stream $port) {
      $this->_serialPort = $port;
    }

    public function buffer(Buffer $buffer = NULL) {
      if (isset($buffer)) {
        $this->_buffer = $buffer;
      } elseif (NULL === $this->_buffer) {
        $this->_buffer = new Buffer();
      }
      return $this->_buffer;
    }

    public function port() {
      return $this->_serialPort;
    }

    public function activate(Callable $callback) {
      $this->port()->events()->on('error', $callback);
      $this->port()->events()->on('data', array($this->buffer(), 'addData'));
      $this->buffer()->events()->on('response', array($this, 'onResponse'));
      $this->_activationCallback = $callback;
      if ($this->port()->open()) {
        $this->port()->write([COMMAND_REPORT_VERSION]);
        return TRUE;
      } else {
        return FALSE;
      }
    }

    public function getVersion() {
      return $this->_version;
    }

    public function onResponse(Response $response) {
      switch ($response->command()) {
      case COMMAND_REPORT_VERSION :
        $this->_version['major'] = $response->major;
        $this->_version['minor'] = $response->minor;
        $this->events()->emit('reportversion', $this->_version);
        if ($this->_activationCallback) {
          call_user_func($this->_activationCallback);
          $this->port()->write([COMMAND_START_SYSEX, COMMAND_QUERY_FIRMWARE, COMMAND_END_SYSEX]);
        }
        break;
      }
    }


    public function pinMode($pin, $mode) {
      $this->_pins[$pin]['mode'] = $mode;
      $this->port()->write([COMMAND_PIN_MODE, $pin, $mode]);
    }

    public function digitalWrite($pin, $value) {
      $port = floor($pin / 8);
      $portValue = 0;
      $this->_pins[$pin]['value'] = $value;
      for ($i = 0; $i < 8; $i++) {
        if (!empty($this->_pins[8 * $port + $i]['value'])) {
          $portValue |= (1 << $i);
        }
      }
      $this->_serialPort->write(
        [COMMAND_DIGITAL_MESSAGE | $port, $portValue & 0x7F, ($portValue >> 7) & 0x7F]
      );
    }
  }
}
