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
          if (FALSE !== ($line = $this->readStatusLine())) {
            $this->_request = new Request($this);
            $this->_request->parseStatus($line);
          }
        }
        if (isset($this->_request)) {
          if ($this->_status == self::STATUS_EXPECT_HEADER) {
            while ($header = $this->readHeader()) {
              $this->_request->parseHeader($header);
            }
          }
          if ($this->_status == self::STATUS_EXPECT_BODY) {
            $this->events()->emit('request', $this->_request);
          }
        }
      }
    }

    private function readStatusLine() {
      if ($position = strpos($this->_buffer, "\n", $this->_bufferOffset)) {
        $result = substr(
          $this->_buffer, $this->_bufferOffset, $position - $this->_bufferOffset + 1
        );
        $this->_bufferOffset = $position + 1;
        $this->_status = self::STATUS_EXPECT_HEADER;
        return $result;
      }
      return FALSE;
    }

    private function readHeader() {
      $result = '';
      $offset = $this->_bufferOffset;
      while (FALSE !== ($position = strpos($this->_buffer, "\n", $offset))) {
        $nextCharacter = substr($this->_buffer, $position + 1, 1);
        $result .= substr($this->_buffer, $offset, $position - $offset + 1);
        $offset = $position + 1;
        switch ($nextCharacter) {
        case "\r" :
        case "\n" :
          $this->_status = self::STATUS_EXPECT_BODY;
          $this->_bufferOffset = strpos($this->_buffer, "\n", $offset) + 1;
          return $result;
        case " " :
        case "\t" :
          continue;
        default :
          $this->_bufferOffset = $offset;
          return $result;
        }
      }
      return FALSE;
    }
  }
}