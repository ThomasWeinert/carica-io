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
   *
   * @property-read array $version
   * @property-read array $firmware
   * @property-read array $pins
   */
  class Board {

    use Event\Emitter\Aggregation;

    /**
     * @var array
     */
    private $_pins = NULL;

    /**
     * @var array
     */
    private $_channels = array();

    /**
     * @var Carica\Io\Stream
     */
    private $_stream = NULL;
    /**
     * @var Buffer
     */
    private $_buffer = NULL;

    /**
     * Firmata version information
     * @var Carica\Io\Firmata\Version
     */
    private $_version = NULL;

    /**
     * Firmware version information
     * @var Carica\Io\Firmata\Version
     */
    private $_firmware= NULL;

    /**
     * Map command responses to private event handlers
     * @var array(integer=>string)
     */
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
     * @param Carica\Io\Stream $port
     */
    public function __construct(Io\Stream $stream) {
      $this->_stream = $stream;
      $this->_pins = new \ArrayObject();
    }

    /**
     * Validate if the board ist still active (the port/stream contains a valid resource)
     *
     * @return boolean
     */
    public function isActive() {
      return is_resource($this->port()->resource());
    }

    /**
     * Getter for the port/stream object
     *
     * @return Carica\Io\Stream
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
     * @param Callable|NULL $callback
     * @return Carica\Io\Deferred\Promise
     */
    public function activate(Callable $callback = NULL) {
      $defer = new \Carica\Io\Deferred();
      if (isset($callback)) {
        $defer->always($callback);
      }
      $this->port()->events()->on(
        'error',
        function($message) use ($defer) {
          $defer->reject($message);
        }
      );
      $this->port()->events()->on('read-data', array($this->buffer(), 'addData'));
      $this->buffer()->events()->on('response', array($this, 'onResponse'));
      if ($this->port()->open()) {
        $board = $this;
        $board->reportVersion(
          function() use ($board, $defer) {
            $board->queryFirmware(
              function() use ($board, $defer) {
                $board->queryCapabilities(
                  function() use ($board, $defer) {
                    $board->queryAnalogMapping(
                      function() use ($defer) {
                        $defer->resolve();
                      }
                    );
                  }
                );
              }
            );
          }
        );
      }
      return $defer->promise();
    }

    /**
     * Provide some read only properties
     *
     * @param string $name
     * @throws LogicException
     * @return mixed
     */
    public function __get($name) {
      switch ($name) {
      case 'version' :
        return isset($this->_version) ? $this->_version : new Version(0,0);
      case 'firmware' :
        return isset($this->_firmware) ? $this->_firmware : new Version(0,0);
      case 'pins' :
        return $this->_pins;
      }
      throw new \LogicException(sprintf('Unknown property %s::$%s', __CLASS__, $name));
    }

    /**
     * Callback for the buffer, received a response from the board. Call a more specific
     * private event handler based on the $_responseHandler mapping array
     *
     * @param Carica\Io\Firmata\Response $response
     */
    public function onResponse(Response $response) {
      if (isset($this->_responseHandler[$response->command()])) {
        $callback = array($this, $this->_responseHandler[$response->command()]);
        return $callback($response);
      }
    }

    /**
     * A version was reported, store it and request value reading
     *
     * @param Carica\Io\Firmata\Response\Midi\ReportVersion $response
     */
    private function onReportVersion(Response\Midi\ReportVersion $response) {
      $this->_version = new Version($response->major, $response->minor);
      for ($i = 0; $i < 16; $i++) {
        $this->port()->write([COMMAND_REPORT_DIGITAL | $i, 1]);
        $this->port()->write([COMMAND_REPORT_ANALOG | $i, 1]);
      }
      $this->events()->emit('reportversion');
    }

    /**
     * Firmware was reported, store it and emit event
     *
     * @param Response\Sysex\QueryFirmware $response
     */
    private function onQueryFirmware(Response\Sysex\QueryFirmware $response) {
      $this->_firmware = new Version($response->major, $response->minor, $response->name);
      $this->events()->emit('queryfirmware');
    }


    /**
     * Capabilities for all pins were reported, store pin status and emit event
     *
     * @param Response\Sysex\CapabilityResponse $response
     */
    private function onCapabilityResponse(Response\Sysex\CapabilityResponse $response) {
      $this->_pins = new \ArrayObject();
      foreach ($response->pins as $pin => $modes) {
        $this->_pins[$pin] = new Pin($this, $pin, $modes);
      }
      $this->events()->emit('capability-query');
    }

    /**
     * Analog mapping data was reported, store it and report event
     *
     * @param Response\Sysex\AnalogMappingResponse $response
     */
    private function onAnalogMappingResponse(Response\Sysex\AnalogMappingResponse $response) {
      $this->_channels = $response->channels;
      $this->events()->emit('analog-mapping-query');
    }


    /**
     * Got an analog message, change pin value and emit events
     *
     * @param Response\Midi\AnalogMessage $response
     */
    private function onAnalogMessage(Response\Midi\AnalogMessage $response) {
      if (isset($this->_channels[$response->port]) &&
          isset($this->_pins[$this->_channels[$response->port]])) {
        $pin = $this->_channels[$response->port];
        $this->events()->emit('analog-read-'.$pin, $response->value);
        $this->events()->emit('analog-read', ['pin' => $pin, 'value' => $response->value]);
      }
    }

    /**
     * Got a digital message, change pin value and emit events
     *
     * @param Response\Midi\DigitalMessage $response
     */
    private function onDigitalMessage(Response\Midi\DigitalMessage $response) {
      for ($i = 0; $i < 8; $i++) {
        if (isset($this->_pins[8 * $response->port + $i])) {
          $pinNumber = 8 * $response->port + $i;
          $pin = $this->_pins[$pinNumber];
          if ($pin->mode == PIN_STATE_INPUT) {
            $value = ($response->value >> ($i & 0x07)) & 0x01;
          } else {
            $value = $pin->value;
          }
          $this->events()->emit('digital-read-'.$pinNumber, $value);
          $this->events()->emit('digital-read', ['pin' => $pinNumber, 'value' => $value]);
        }
      }
    }

    /**
     * Pin status was reported, store it and emit event
     *
     * @param Response\Sysex\PinStateResponse $response
     */
    private function onPinStateResponse(Response\Sysex\PinStateResponse $response) {
      $this->events()->emit('pin-state-'.$response->pin, $response->value);
    }

    /**
     * Reset board
     */
    public function reset() {
      $this->port()->write([COMMAND_SYSTEM_RESET]);
    }

    /**
     * Request version from board and execute callback after it is recieved.
     *
     * @param callable $callback
     */
    public function reportVersion(Callable $callback) {
      $this->events()->once('reportversion', $callback);
      $this->port()->write([COMMAND_REPORT_VERSION]);
    }

    /**
     * Request firmware from board and execute callback after it is recieved.
     *
     * @param callable $callback
     */
    public function queryFirmware(Callable $callback) {
      $this->events()->once('queryfirmware', $callback);
      $this->port()->write([COMMAND_START_SYSEX, COMMAND_QUERY_FIRMWARE, COMMAND_END_SYSEX]);
    }

    /**
     * Query pin capabilities and execute callback after they are recieved
     *
     * @param callable $callback
     */
    public function queryCapabilities(Callable $callback) {
      $this->events()->once('capability-query', $callback);
      $this->port()->write([COMMAND_START_SYSEX, COMMAND_CAPABILITY_QUERY, COMMAND_END_SYSEX]);
    }

    /**
     * Request the analog mapping data and execute callback after it is recieved
     *
     * @param callable $callback
     */
    public function queryAnalogMapping(Callable $callback) {
      $this->events()->once('analog-mapping-query', $callback);
      $this->port()->write([COMMAND_START_SYSEX, COMMAND_ANALOG_MAPPING_QUERY, COMMAND_END_SYSEX]);
    }

    /**
     * Query pin status (mode and value), and execute callback after it recieved
     *
     * @param integer $pin 0-16
     * @param callable $callback
     */
    public function queryPinState($pin, Callable $callback) {
      $this->events()->once('pin-state-'.$pin, $callback);
      $this->port()->write([COMMAND_START_SYSEX, COMMAND_PIN_STATE_QUERY, $pin, COMMAND_END_SYSEX]);
    }
    /**
     * Add a callback for analog read events on a pin
     *
     * @param integer $pin 0-16
     */
    public function analogRead($pin, $callback) {
      $this->events()->on('analog-read-'.$pin, $callback);
    }

    /**
     * Add a callback for diagital read events on a pin
     * @param integer $pin 0-16
     */
    public function digitalRead($pin, $callback) {
      $this->events()->on('digital-read-'.$pin, $callback);
    }


    /**
     * Write an analog value for a pin
     *
     * @param integer $pin 0-16
     * @param integer $value 0-255
     */
    public function analogWrite($pin, $value) {
      $this->_pins[$pin]->setAnalog($value);
      $this->port()->write(
        [COMMAND_ANALOG_MESSAGE | $pin, $value & 0x7F, ($value >> 7) & 0x7F]
      );
    }

    /**
     * Move a servo - an alias for analogWrite()
     *
     * @param integer $pin 0-16
     * @param integer $value 0-255
     */
    public function servoWrite($pin, $value) {
      $this->analogWrite($pin, $value);
    }

    /**
     * Write a digital value for a pin (on/off, DIGITAL_LOW/DIGITAL_HIGH)
     *
     * @param integer $pin 0-16
     * @param integer $value 0-1
     */
    public function digitalWrite($pin, $value) {
      $this->_pins[$pin]->setDigital($value == DIGITAL_HIGH);
      $port = floor($pin / 8);
      $portValue = 0;
      for ($i = 0; $i < 8; $i++) {
        if ($this->_pins[8 * $port + $i]->digital) {
          $portValue |= (1 << $i);
        }
      }
      $this->port()->write(
        [COMMAND_DIGITAL_MESSAGE | $port, $portValue & 0x7F, ($portValue >> 7) & 0x7F]
      );
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
      $this->_pins[$pin]->setMode($mode);
    }
  }
}
