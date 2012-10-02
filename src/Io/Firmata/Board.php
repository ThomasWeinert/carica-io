<?php

namespace Carica\Io\Firmata {

  use Carica\Io\Event;
  use Carica\Io\Stream;

  const INPUT = 0x00;
  const OUTPUT = 0x01;
  const ANALOG = 0x02;
  const PWM = 0x03;
  const SERVO = 0x04;

  const PIN_MODE = 0xF4;
  const REPORT_DIGITAL = 0xD0;
  const REPORT_ANALOG = 0xC0;
  const DIGITAL_MESSAGE = 0x90;
  const START_SYSEX = 0xF0;
  const END_SYSEX = 0xF7;
  const QUERY_FIRMWARE = 0x79;
  const REPORT_VERSION = 0xF9;
  const ANALOG_MESSAGE = 0xE0;
  const CAPABILITY_QUERY = 0x6B;
  const CAPABILITY_RESPONSE = 0x6C;
  const PIN_STATE_QUERY = 0x6D;
  const PIN_STATE_RESPONSE = 0x6E;
  const ANALOG_MAPPING_QUERY = 0x69;
  const ANALOG_MAPPING_RESPONSE = 0x6A;
  const I2C_REQUEST = 0x76;
  const I2C_REPLY = 0x77;
  const I2C_CONFIG = 0x78;
  const STRING_DATA = 0x71;
  const SYSTEM_RESET = 0xFF;

  const LOW = 0;
  const HIGH = 1;
  const MAX_DATA_BYTES = 32;

  class Board {

    use Event\Emitter\Aggregation;

    private $_pins = array();
    private $_serialPort = NULL;

    private $_versionReceived = NULL;

    public function __construct(Stream\SerialPort $port) {
      $this->_serialPort = $port;
    }

    public function activate($callback) {
      $this->_serialPort->events()->on('error', $callback);
      $this->_serialPort->events()->on('data', array($this, 'onData'));
      if ($this->_serialPort->open()) {
        $this->_serialPort->write(REPORT_VERSION);
        return TRUE;
      } else {
        return FALSE;
      }
    }

    public function onData($data) {
      if (!$this->_versionReceived && $data[0] != REPORT_VERSION) {
        echo dechex(ord($data[0]));
        return;
      } else {
        $this->_versionReceived = TRUE;
      }
      echo 'DATA.';
    }

    public function pinMode($pin, $mode) {
      $this->_pins[$pin]['mode'] = $mode;
      $this->_serialPort->write([PIN_MODE, $pin, $mode]);
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
        [DIGITAL_MESSAGE | $port, $portValue & 0x7F, ($portValue >> 7) & 0x7F]
      );
    }

    public function reset() {
      $this->_serialPort->write([SYSTEM_RESET]);
    }
  }
}
