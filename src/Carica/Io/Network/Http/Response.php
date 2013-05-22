<?php

namespace Carica\Io\Network\Http {

  use Carica\Io;

  class Response {

    public $_version = '1.0';

    private $_status = 200;
    private $_statusStrings = array(
      200 => 'OK'
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
        return;
      case 'status' :
        $this->setStatus($value);
        return;
      case 'connection' :
      case 'content' :
      case 'headers' :
        return call_user_func(array($this, $name), $value);
      }
      throw new \LogicException(
        sprintf('Unknown property %s::$%s', get_class($this), $name)
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
      if (isset($this->_statusString[$status])) {
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
      $this->headers['Content-Type'] = $this->content()->type;
      $this->headers['Content-Length'] = $this->content()->length;
      $this->connection()->write(
        sprintf(
          "HTTP/%s %s %s\n",
          $this->_version,
          $this->_status,
          $this->_statusStrings[$this->_status]
        )
      );
      foreach ($this->headers() as $header) {
        foreach ($header as $value) {
          $this->connection()->write($header->name.": ".$value."\n");
        }
      }
      $this->connection()->write("\n");
      $this->content()->sendTo($this->connection());
    }
  }
}