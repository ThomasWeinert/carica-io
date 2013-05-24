<?php

namespace Carica\Io\Network\Http {

  use Carica\Io;

  class Request {

    private $_patternStatus =
      '(^(?P<method>[A-Z]+) (?P<url>\S+) HTTP/(?P<version>1\.\d)\r?\n)';

    public $_method = 'GET';
    public $_version = '1.0';
    public $_url = '/';
    public $_path = '/';

    private $_connection = NULL;
    private $_headers = NULL;
    private $_query = NULL;

    public function __construct(Connection $connection) {
      $this->connection($connection);
      $this->_headers = new Headers();
      $this->_query = new Request\Query();
    }

    public function __get($name) {
      switch ($name) {
      case 'method' :
      case 'version' :
      case 'url' :
      case 'path' :
        return $this->{'_'.$name};
      case 'connection' :
      case 'headers' :
      case 'query' :
        return call_user_func(array($this, $name));
      }
      throw new \LogicException(
        sprintf('Unknown property %s::$%s', get_class($this), $name)
      );
    }

    public function connection(Connection $connection = NULL) {
      if (isset($connection)) {
        $this->_connection = $connection;
      }
      return $this->_connection;
    }

    public function headers(Headers $headers = NULL) {
      if (isset($headers)) {
        $this->_headers = $headers;
      }
      return $this->_headers;
    }

    public function query(Request\Query $query = NULL) {
      if (isset($query)) {
        $this->_query = $query;
      }
      return $this->_query;
    }

    public function parseStatus($line) {
      if (preg_match($this->_patternStatus, $line, $matches)) {
        $this->method = $matches['method'];
        $this->version = $matches['version'];
        $this->url = $matches['url'];
        $parsedUrl = parse_url($matches['url']);
        $this->path = empty($parsedUrl['path']) ? '' : $parsedUrl['path'];
        $this->query->setQueryString(
          empty($parsedUrl['query']) ? '' : $parsedUrl['query']
        );
      }
    }

    public function parseHeader($string) {
      try {
        $this->_headers[] = $string;
      } catch (\UnexpectedValueException $e) {
        // ignore invalid headers
      }
    }

    public function createResponse() {
      return new Response($this);
    }
  }
}