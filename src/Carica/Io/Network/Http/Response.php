<?php

namespace Carica\Io\Network\Http {

  use Carica\Io;

  /**
   * @property string $version
   * @property integer $status
   * @property Connection $connection
   * @property Headers $headers
   * @property Response\Content $content
   */
  class Response {

    public $_version = '1.0';

    private $_status = 200;

    protected $_statusStrings = array(
      100 => 'Continue',
      101 => 'Switching Protocols',
      102 => 'Processing',
      200 => 'OK',
      201 => 'Created',
      202 => 'Accepted',
      203 => 'Non-Authoritative Information',
      204 => 'No Content',
      205 => 'Reset Content',
      206 => 'Partial Content',
      207 => 'Multi-Status',
      300 => 'Multiple Choices',
      301 => 'Moved Permanently',
      302 => 'Found',
      303 => 'See Other',
      304 => 'Not Modified',
      305 => 'Use Proxy',
      307 => 'Temporary Redirect',
      400 => 'Bad Request',
      401 => 'Authorization Required',
      402 => 'Payment Required',
      403 => 'Forbidden',
      404 => 'Not Found',
      405 => 'Method Not Allowed',
      406 => 'Not Acceptable',
      407 => 'Proxy Authentication Required',
      408 => 'Request Time-out',
      409 => 'Conflict',
      410 => 'Gone',
      411 => 'Length Required',
      412 => 'Precondition Failed',
      413 => 'Request Entity Too Large',
      414 => 'Request-URI Too Large',
      415 => 'Unsupported Media Type',
      416 => 'Requested Range Not Satisfiable',
      417 => 'Expectation Failed',
      422 => 'Unprocessable Entity',
      423 => 'Locked',
      424 => 'Failed Dependency',
      425 => 'No code',
      426 => 'Upgrade Required',
      500 => 'Internal Server Error',
      501 => 'Method Not Implemented',
      502 => 'Bad Gateway',
      503 => 'Service Temporarily Unavailable',
      504 => 'Gateway Time-out',
      505 => 'HTTP Version Not Supported',
      506 => 'Variant Also Negotiates',
      507 => 'Insufficient Storage',
      510 => 'Not Extended'
    );

    private $_connection = NULL;
    private $_headers = NULL;
    private $_content = NULL;

    public function __construct(Connection $connection) {
      $this->connection($connection);
      $this->_headers = new Headers();
    }

    public function __get($name) {
      switch ($name) {
      case 'version' :
      case 'status' :
        return $this->{'_'.$name};
      case 'connection' :
      case 'content' :
      case 'headers' :
        return call_user_func(array($this, $name));
      }
      throw new \LogicException(
        sprintf('Unknown property %s::$%s', get_class($this), $name)
      );
    }

    public function __set($name, $value) {
      switch ($name) {
      case 'version' :
        $this->setVersion($value);
        return NULL;
      case 'status' :
        $this->setStatus($value);
        return NULL;
      case 'connection' :
      case 'content' :
      case 'headers' :
        return call_user_func(array($this, $name), $value);
      }
      throw new \LogicException(
        sprintf('Can not write unknown property %s::$%s', get_class($this), $name)
      );
    }

    public function setVersion($version) {
      if (in_array($version, array('1.0', '1.1'))) {
        $this->_version = $version;
      } else {
        throw \InvalidArgumentException(
          sprintf('Invalid http version string %s', $version)
        );
      }
    }

    public function setStatus($status) {
      $status = (int)$status;
      if (isset($this->_statusStrings[$status])) {
        $this->_status = $status;
      } else {
        throw \InvalidArgumentException(
          sprintf('Invalid http status code %d', $status)
        );
      }
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

    public function content(Response\Content $content = NULL) {
      if (isset($content)) {
        $this->_content = $content;
      } elseif (NULL === $this->_content) {
        $this->_content = new Response\Content\String('text/plain');
      }
      return $this->_content;
    }

    public function send() {
      $connection = $this->connection();
      $contentType = $this->content()->type;
      $encoding = $this->content()->encoding;
      if (!empty($encoding)) {
        $contentType .= '; charset='.$encoding;
      }
      $this->headers['Content-Type'] = $contentType;
      $this->headers['Content-Length'] = $this->content()->length;
      $connection->write(
        sprintf(
          "HTTP/%s %s %s\n",
          $this->_version,
          $this->_status,
          $this->_statusStrings[$this->_status]
        )
      );
      foreach ($this->headers() as $header) {
        foreach ($header as $value) {
          $connection->write($header->name.": ".$value."\n");
        }
      }
      $connection->write("\n");
      return Io\Deferred::When(
        $this->content()->sendTo($connection)
      );
    }
  }
}