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
          $this->handleSysexResponse($this->_bytes);
          $this->_bytes = array();
        } elseif ($byteCount == 3 && $first !== COMMAND_START_SYSEX) {
          $command = ($first < 240) ? $first & 0xF0 : $first;
          $this->handleMidiResponse(array($command, $this->_bytes[1], $this->_bytes[2]));
          $this->_bytes = array();
        }
      }
    }

    private function handleSysexResponse($bytes) {
      array_shift($bytes);
      array_pop($bytes);
      $command = array_shift($bytes);
      $response = NULL;
      switch ($command) {
      case COMMAND_QUERY_FIRMWARE :
        break;
      }
      if ($response) {
        $this->events()->emit('response', $response);
      }
    }

    private function handleMidiResponse($bytes) {
      $command = $bytes[0];
      $response = NULL;
      switch ($command) {
      case COMMAND_REPORT_VERSION :
        $response = new Response\ReportVersion($bytes);
        break;
      case COMMAND_PIN_MODE :

        break;
      }
      if ($response) {
        $this->events()->emit('response', $response);
      }
    }
  }
}
