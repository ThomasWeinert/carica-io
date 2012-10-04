<?php

namespace Carica\Io\Network\Http {

  use Carica\Io;

  class Request extends Io\Network\Connection {

    const STATUS_INVALID = 0;
    const STATUS_CONNECTED = 1;
    const STATUS_STATUS_RECIEVED = 2;
    const STATUS_HEADERS_RECIEVED = 3;

    const PATTERN_STATUS = '(^(?P<method>[A-Z]+) (?P<url>\S+) HTTP/(?P<version>1\.\d)\r?\n)';
    const STATUS_END = "\r\n";
    const HEADER_END = "\r\n\r\n";

    private $_status = self::STATUS_CONNECTED;
    private $_buffer = '';

    public $method = 'GET';
    public $version = '1.0';
    public $url = '/';

    private $_headers = NULL;

    public function headers(Headers $headers = NULL) {
      if (isset($headers)) {
        $this->_headers = $headers;
      } elseif (NULL === $this->_headers) {
        $this->_headers = new Headers();
      }
      return $this->_headers;
    }

    public function read($bytes = 1024) {
      if ($data = parent::read($bytes)) {
        $this->_buffer .= $data;
        if ($this->_status == self::STATUS_CONNECTED &&
            ($p = strpos($this->_buffer, self::STATUS_END))) {
          if (preg_match(self::PATTERN_STATUS, $this->_buffer, $matches)) {
            $this->_buffer = substr($this->_buffer, $p + strlen(self::STATUS_END));
            $this->_status = self::STATUS_STATUS_RECIEVED;
            $this->method = $matches['method'];
            $this->version = $matches['version'];
            $this->url = $matches['url'];
            $this->events()->emit('status', $this);
          } else {
            $this->_status = self::STATUS_INVALID;
            $this->events()->emit('error', $this);
          }
        }
        if ($this->_status == self::STATUS_STATUS_RECIEVED &&
            ($p = strpos($this->_buffer, self::HEADER_END))) {
          $this->headers()->setString(substr($this->_buffer, 0, $p));
          $this->_buffer = substr($this->_buffer, $p + strlen(self::HEADER_END));
          $this->_status = self::STATUS_HEADERS_RECIEVED;
          $this->events()->emit('headers', $this);
        } else {
          $this->_status = self::STATUS_INVALID;
          $this->events()->emit('error', $this);
        }
      }
    }
  }
}