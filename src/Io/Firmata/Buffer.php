<?php

namespace Carica\Io\Firmata {

  class Buffer {

    use Event\Emitter\Aggregation;

    private $_bytes = array();
    private $_versionReceived = FALSE;

    public function addData($data) {
      if (count($this->_bytes) == 0) {
        $data = ltrim($data, pack('C', 0));
      }
      $bytes = array_slice(unpack("C*", "\0".$data), 1);
      array_splice($this->_bytes, count($this->_bytes), 0, $bytes);
      if (count($this->_bytes) > 0) {
        while (!$this->_versionReceived &&
               count($this->_bytes) > 0 &&
               $this->_bytes[0] != REPORT_VERSION) {
          array_shift($this->_bytes);
        }
        if (count($this->_bytes) > 0 && $this->_bytes[0] == START_SYSEX) {
          $end = array_search($this->_bytes, END_SYSEX);
          $this->handleSysexResponse(array_slice($this->_bytes, 0, $end));
        } elseif (count($this->_bytes) > 2) {
          list($command) = $bytes;
          if ($command == REPORT_VERSION) {
            $this->_versionReceived = TRUE;
          }
          $this->handleMidiResponse(array_slice($this->_bytes, 0, 3));
        }
      }
    }

    private function handleSysexResponse($bytes) {
      array_shift($bytes);
      array_pop($bytes);
      $command = array_shift($bytes);
      $response = NULL;
      switch ($command) {
      case QUERY_FIRMWARE :

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
      case REPORT_VERSION :
        $response = new Response\ReportVersion($bytes);
        break;
      case PIN_MODE :

        break;
      }
      if ($response) {
        $this->events()->emit('response', $response);
      }
    }
  }
}
