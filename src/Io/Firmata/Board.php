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

  /**
   * This class represents an Arduino board running firmata.
   */
  class Board {

    use Event\Emitter\Aggregation;

    private $_pins = array();
    private $_channels = array();

    private $_stream = NULL;
    private $_buffer = NULL;

    private $_version = array(
      'major' => 0,
      'minor' => 0
    );
    private $_firmware = array(
      'name' => '',
      'version' => array(
        'major' => 0,
        'minor' => 0
      )
    );

    private $_responseHandler = array(
      COMMAND_REPORT_VERSION => 'onReportVersion',
      COMMAND_ANALOG_MESSAGE => 'onAnalogMessage',
      COMMAND_DIGITAL_MESSAGE => 'onDigitalMessage',
      COMMAND_QUERY_FIRMWARE => 'onQueryFirmware',
      COMMAND_CAPABILITY_RESPONSE => 'onCapabilityResponse',
      COMMAND_PIN_STATE_RESPONSE => 'onPinStateResponse',
      COMMAND_ANALOG_MAPPING_RESPONSE => 'onAnalogMappingResponse'
    );

    /**
     * Create board and assign stream object
     *
     * @param Io\Stream $port
     */
    public function __construct(Io\Stream $stream) {
      $this->_stream = $stream;
    }

    /**
     * Getter for the port/stream object
     *
     * @return Io\Stream
     */
    public function port() {
      return $this->_stream;
    }

    /**
     * Buffer for recieved data
     *
     * @param Buffer $buffer
     */
    public function buffer(Buffer $buffer = NULL) {
      if (isset($buffer)) {
        $this->_buffer = $buffer;
      } elseif (NULL === $this->_buffer) {
        $this->_buffer = new Buffer();
      }
      return $this->_buffer;
    }

    /**
     * Activate the board, assign the needed callbacks
     *
     * @param callable $callback
     * @return boolean
     */
    public function activate(Callable $callback) {
      $this->port()->events()->on('error', $callback);
      $this->port()->events()->on('data', array($this->buffer(), 'addData'));
      $this->buffer()->events()->on('response', array($this, 'onResponse'));
      if ($this->port()->open()) {
        $board = $this;
        $board->reportVersion(
          function() use ($board, $callback) {
            $board->queryFirmware(
              function() use ($board, $callback) {
                $board->queryCapabilities(
                  function() use ($board, $callback) {
                    $board->queryAnalogMapping(
                      function() use ($callback) {
                        $callback();
                      }
                    );
                 }
                );
              }
            );
          }
        );
        return TRUE;
      } else {
        return FALSE;
      }
    }

    public function __get($name) {
      switch ($name) {
      case 'version' :
        return $this->_version;
      case 'firmware' :
        return $this->_firmware;
      case 'pins' :
        return $this->_pins;
      }
      throw new \LogicException(sprintf('Unknown property %s::$%s', __CLASS__, $name));
    }

    /**
     * Callback for the buffer, recieved a response from the board.
     *
     * @param Response $response
     */
    public function onResponse(Response $response) {
      $that = $this;
      if (isset($this->_responseHandler[$response->command()])) {
        $callback = array($this, $this->_responseHandler[$response->command()]);
        return $callback($response);
      }
    }

    private function onReportVersion(Response\Midi\ReportVersion $response) {
      $this->_version['major'] = $response->major;
      $this->_version['minor'] = $response->minor;
      for ($i = 0; $i < 16; $i++) {
        $this->port()->write([COMMAND_REPORT_DIGITAL | $i, 1]);
        $this->port()->write([COMMAND_REPORT_ANALOG | $i, 1]);
      }
      $this->events()->emit('reportversion');
    }

    private function onAnalogMessage(Response\Midi\AnalogMessage $response) {
      if (isset($this->_channels[$response->pin]) &&
          $this->_pins[$this->_channels[$response->pin]]) {
        $this->_pins[$this->_channels[$response->pin]]['value'] = $response->value;
      }
      $this->events()->emit('analog-read-' + $response->pin, $response->value);
      $this->events()->emit('analog-read', ['pin' => $response->pin, 'value' => $response->value]);
    }

    private function onDigitalMessage(Response\Midi\DigitalMessage $response) {
      for ($i = 0; $i < 8; $i++) {
        if (isset($this->_pins[8 * $response->pin + $i])) {
          $pinNumber = 8 * $response->pin + $i;
          $pin =& $this->_pins[$pinNumber];
          if ($pin['mode'] == PIN_STATE_INPUT) {
            $pin['value'] = ($response->value >> ($i & 0x07)) & 0x01;
          }
          $this->events()->emit('digital-read-' + $pinNumber, $pin['value']);
          $this->events()->emit('digital-read', ['pin' => $pinNumber, 'value' => $pin['value']]);
        }
      }
    }

    private function onQueryFirmware(Response\Sysex\QueryFirmware $response) {
      $this->_firmware['name'] = $response->name;
      $this->_firmware['version']['major'] = $response->major;
      $this->_firmware['version']['minor'] = $response->minor;
      $this->events()->emit('queryfirmware');
    }

    private function onCapabilityResponse(Response\Sysex\CapabilityResponse $response) {
      $this->_pins = $response->pins;
      var_dump('Capability');
      $this->events()->emit('capability-query');
    }

    private function onPinStateResponse(Response\Sysex\PinStateResponse $response) {
      $this->_pins[$response->pin]['mode'] = $response->mode;
      $this->_pins[$response->pin]['value'] = $response->value;
      $this->events()->emit('pin-state-'.$response->pin);
    }

    private function onAnalogMappingResponse(Response\Sysex\AnalogMappingResponse $response) {
      $this->_channels = $response->channels;
      $this->events()->emit('analog-mapping-query');
    }


    public function reset() {
      $this->port()->write([COMMAND_SYSTEM_RESET]);
    }

    public function reportVersion(Callable $callback) {
      $this->events()->once('reportversion', $callback);
      $this->port()->write([COMMAND_REPORT_VERSION]);
    }

    public function queryFirmware(Callable $callback) {
      $this->events()->once('queryfirmware', $callback);
      $this->port()->write([COMMAND_START_SYSEX, COMMAND_QUERY_FIRMWARE, COMMAND_END_SYSEX]);
    }

    public function queryCapabilities(Callable $callback) {
      $this->events()->once('capability-query', $callback);
      $this->port()->write([COMMAND_START_SYSEX, COMMAND_CAPABILITY_QUERY, COMMAND_END_SYSEX]);
    }

    public function queryAnalogMapping(Callable $callback) {
      $this->events()->once('analog-mapping-query', $callback);
      $this->port()->write([COMMAND_START_SYSEX, COMMAND_ANALOG_MAPPING_QUERY, COMMAND_END_SYSEX]);
    }

    public function queryPinState($pin, Callable $callback) {
      $this->events()->once('pin-state-'.$pin, $callback);
      $this->port()->write([COMMAND_START_SYSEX, COMMAND_PIN_STATE_QUERY, pin, COMMAND_END_SYSEX]);
    }

    /**
     * Set the mode of a pin:
     *   PIN_STATE_INPUT,
     *   PIN_STATE_OUTPUT,
     *   PIN_STATE_ANALOG,
     *   PIN_STATE_PWM,
     *   PIN_STATE_SERVO
     *
     * @param integer $pin 0-16
     * @param integer $mode
     */
    public function pinMode($pin, $mode) {
      $this->_pins[$pin]['mode'] = $mode;
      $this->port()->write([COMMAND_PIN_MODE, $pin, $mode]);
    }

    /**
     * Write a digital value for a pin (on/off, DIGITAL_LOW/DIGITAL_HIGH)
     *
     * @param integer $pin 0-16
     * @param integer $value 0-1
     */
    public function digitalWrite($pin, $value) {
      $port = floor($pin / 8);
      $portValue = 0;
      $this->_pins[$pin]['value'] = $value;
      for ($i = 0; $i < 8; $i++) {
        if (!empty($this->_pins[8 * $port + $i]['value'])) {
          $portValue |= (1 << $i);
        }
      }
      $this->port()->write(
        [COMMAND_DIGITAL_MESSAGE | $port, $portValue & 0x7F, ($portValue >> 7) & 0x7F]
      );
    }

    /**
     * Write an analog value for a pin
     *
     * @param integer $pin 0-16
     * @param integer $value 0-255
     */
    public function analogWrite($pin, $value) {
      $this->_pins[$pin]['value'] = $value;
      $this->port()->write(
        [COMMAND_ANALOG_MESSAGE | $pin, $value & 0x7F, ($value >> 7) & 0x7F]
      );
    }
  }
}
