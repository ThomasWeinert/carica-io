<?php

namespace Carica\Io\Network\Http {

  use Carica\Io;

  class Request {

    private $_patternStatus =
      '(^(?P<method>[A-Z]+) (?P<url>\S+) HTTP/(?P<version>1\.\d)\r?\n)';

    public $method = 'GET';
    public $version = '1.0';
    public $url = '/';

    private $_connection = NULL;

    public function __construct(Connection $connection) {
      $this->Connection($connection);
    }

    public function Connection(Connection $connection = NULL) {
      if (isset($connection)) {
        $this->_connection = $connection;
      }
      return $this->_connection;
    }

    public function parseStatus($line) {
      if (preg_match($this->_patternStatus, $line, $matches)) {
        $this->method = $matches['method'];
        $this->url = $matches['url'];
        $this->version = $matches['version'];
      }
    }

    public function parseHeader($string) {
      var_dump($string);
    }
  }
}