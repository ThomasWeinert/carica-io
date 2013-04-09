<?php

namespace Carica\Io\Network\Http {

  use Carica\Io;

  class Request {

    private $_patternStatus =
      '(^(?P<method>[A-Z]+) (?P<url>\S+) HTTP/(?P<version>1\.\d)\r?\n)';

    public $method = 'GET';
    public $version = '1.0';
    public $url = '/';
    public $path = '/';

    private $_connection = NULL;

    public function __construct(Connection $connection) {
      $this->connection($connection);
    }

    public function connection(Connection $connection = NULL) {
      if (isset($connection)) {
        $this->_connection = $connection;
      }
      return $this->_connection;
    }

    public function parseStatus($line) {
      if (preg_match($this->_patternStatus, $line, $matches)) {
        $this->method = $matches['method'];
        $this->url = $matches['url'];
        $parsedUrl = parse_url($matches['url']);
        $this->path = empty($parsedUrl['path']) ? '' : $parsedUrl['path'];
        $this->version = $matches['version'];
      }
    }

    public function parseHeader($string) {
      //var_dump($string);
    }
  }
}