<?php

namespace Carica\Io\Firmata {

  use Carica\Io;

  class Buffer {

    use Io\Event\Emitter\Aggregation;

    private $_bytes = array();
    private $_versionReceived = FALSE;

    public function addData($data) {
      if (count($this->_bytes) == 0) {
        $data = ltrim($data, pack('C', 0));
      }
      $bytes = array_slice(unpack("C*", "\0".$data), 1);
      foreach ($bytes as $byte) {
        $this->addByte($byte);
      }
    }

    function addByte($byte) {
      if (!$this->_versionReceived) {
        if ($byte !== COMMAND_REPORT_VERSION) {
          return;
        } else {
          $this->_versionReceived = TRUE;
        }
      }
      $byteCount = count($this->_bytes);
      if ($byte == 0 && $byteCount == 0) {
        return;
      } else {
        $this->_bytes[] = $byte;
        ++$byteCount;
      }
      if ($byteCount > 0) {
        $first = reset($this->_bytes);
        $last = end($this->_bytes);
        if ($first === COMMAND_START_SYSEX &&
            $last === COMMAND_END_SYSEX) {
          $this->handleResponse(array_slice($this->_bytes, 1, -1));
          $this->_bytes = array();
        } elseif ($byteCount == 3 && $first !== COMMAND_START_SYSEX) {
          $command = ($first < 240) ? $first & 0xF0 : $first;
          $this->handleResponse(array($command, $this->_bytes[1], $this->_bytes[2]));
          $this->_bytes = array();
        }
      }
    }

    private function handleResponse($bytes) {
      $command = $bytes[0];
      $response = NULL;
      $classes = array(
        COMMAND_REPORT_VERSION => 'Midi\ReportVersion',
        COMMAND_ANALOG_MESSAGE => 'Midi\AnalogMessage',
        COMMAND_DIGITAL_MESSAGE => 'Midi\DigitalMessage',
        COMMAND_QUERY_FIRMWARE => 'Sysex\QueryFirmware',
        COMMAND_CAPABILITY_RESPONSE => 'Sysex\CapabilityResponse',
        COMMAND_PIN_STATE_RESPONSE => 'Sysex\PinStateResponse',
        COMMAND_ANALOG_MAPPING_RESPONSE => 'Sysex\AnalogMappingResponse'
      );
      if (isset($classes[$command])) {
        $className = __NAMESPACE__.'\\Response\\'.$classes[$command];
        $response = new $className($bytes);
      }
      if ($response) {
        $this->events()->emit('response', $response);
      }
    }
  }
}
