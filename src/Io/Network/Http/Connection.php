<?php

namespace Carica\Io\Network\Http {

  use Carica\Io;

  class Connection extends Io\Network\Connection {

    const STATUS_EXPECT_REQUEST = 0;
    const STATUS_EXPECT_HEADER = 1;
    const STATUS_EXPECT_BODY = 2;

    const STATUS_ERROR = -1;

    private $_status = self::STATUS_EXPECT_REQUEST;

    private $_buffer = '';
    private $_bufferOffset = 0;
    private $_request = NULL;

    public function read($bytes = 1024) {
      if ($data = parent::read($bytes)) {
        $this->_buffer .= $data;
        if ($this->_status == self::STATUS_EXPECT_REQUEST) {
          if (FALSE !== ($line = $this->getStatusLine())) {
            $this->_request = new Request($this);
            $this->_request->parseStatus($line);
            $this->events()->emit('request', $this->_request);
          }
        }
      }
    }

    private function getStatusLine() {
      if ($position = strpos($this->_buffer, "\n", $this->_bufferOffset)) {
        $result = substr($this->_buffer, $this->_bufferOffset, $position + 1);
        $this->_bufferOffset = $this->_bufferOffset + $position + 1;
        return $result;
      }
      return FALSE;
    }

    private function isSpaceOrTab($string, $position) {
      $character = substr($string, $position, 1);
      return ($character == ' ' or $character = "\t");
    }
  }
}