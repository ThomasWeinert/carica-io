<?php
declare(strict_types=1);

namespace Carica\Io\Network\Http {

  use LogicException;

  /**
   * @property string $method
   * @property string $version
   * @property string $url
   * @property string $path
   * @property Connection $connection
   * @property Headers $headers
   * @property Request\Query $query
   */
  class Request {

    private $_patternStatus =
      '(^(?P<method>[A-Z]+) (?P<url>\S+) HTTP/(?P<version>1\.\d)\r?\n)';

    public $_method = 'GET';
    public $_version = '1.0';
    public $_url = '/';
    public $_path = '/';

    private $_connection;
    private $_headers;
    private $_query;

    public function __construct(Connection $connection) {
      $this->connection($connection);
      $this->_headers = new Headers();
      $this->_query = new Request\Query();
    }

    public function __isset($name) {
      switch ($name) {
      case 'method' :
      case 'version' :
      case 'url' :
      case 'path' :
      case 'connection' :
      case 'headers' :
      case 'query' :
        return TRUE;
      }
      return FALSE;
    }

    public function __get($name) {
      switch ($name) {
      case 'method' :
        return $this->_method;
      case 'version' :
        return $this->_version;
      case 'url' :
        return $this->_url;
      case 'path' :
        return $this->_path;
      case 'connection' :
        return $this->connection();
      case 'headers' :
        return $this->headers();
      case 'query' :
        return $this->query();
      }
      throw new LogicException(
        sprintf('Unknown property %s::$%s', get_class($this), $name)
      );
    }

    public function __set($name, $value) {
      throw new LogicException('Unknown/Readonly property: '.$name);
    }

    public function connection(Connection $connection = NULL): ?Connection {
      if (isset($connection)) {
        $this->_connection = $connection;
      }
      return $this->_connection;
    }

    public function headers(Headers $headers = NULL): ?Headers {
      if (isset($headers)) {
        $this->_headers = $headers;
      }
      return $this->_headers;
    }

    public function query(Request\Query $query = NULL): ?Request\Query {
      if (isset($query)) {
        $this->_query = $query;
      }
      return $this->_query;
    }

    public function parseStatus(string $line): void {
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

    public function parseHeader(string $string): void {
      try {
        $this->_headers[] = $string;
      } catch (\UnexpectedValueException $e) {
        // ignore invalid headers
      }
    }

    /**
     * @param Response\Content $content
     * @return Response
     */
    public function createResponse(Response\Content $content = NULL): Response {
      $response = new Response($this->connection());
      if (isset($content)) {
        $response->content = $content;
      }
      return $response;
    }
  }
}
