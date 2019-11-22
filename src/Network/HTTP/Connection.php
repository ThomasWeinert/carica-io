<?php
declare(strict_types=1);

namespace Carica\Io\Network\HTTP {

  use Carica\Io\Network\Connection as NetworkConnection;

  class Connection extends NetworkConnection {

    public const EVENT_REQUEST_RECEIVED = 'request';

    private const STATUS_EXPECT_REQUEST = 0;
    private const STATUS_EXPECT_HEADER = 1;
    private const STATUS_EXPECT_BODY = 2;
    private const STATUS_EXPECT_DATA = 3;

    private $_status = self::STATUS_EXPECT_REQUEST;

    private $_buffer = '';
    private $_bufferOffset = 0;
    private $_request;

    public function read(int $bytes = 65535): ?string {
      if ($data = parent::read($bytes)) {
        $this->_buffer .= $data;
        if (
          $this->_status === self::STATUS_EXPECT_REQUEST &&
          FALSE !== ($line = $this->readStatusLine())
        ) {
          $this->_request = new Request($this);
          $this->_request->parseStatus($line);
        }
        if (isset($this->_request)) {
          if ($this->_status === self::STATUS_EXPECT_HEADER) {
            while ($header = $this->readHeader()) {
              $this->_request->parseHeader($header);
            }
          }
          if ($this->_status === self::STATUS_EXPECT_BODY) {
            $this->events()->emit(self::EVENT_REQUEST_RECEIVED, $this->_request);
            $this->_status = self::STATUS_EXPECT_DATA;
          }
        }
      }
      return NULL;
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
        case ' ' :
        case "\t" :
          break;
        default :
          $this->_bufferOffset = $offset;
          return $result;
        }
      }
      return FALSE;
    }
  }
}
