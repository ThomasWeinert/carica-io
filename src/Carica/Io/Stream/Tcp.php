<?php

namespace Carica\Io\Stream {

  use Carica\Io\Event;

  class Tcp implements \Carica\Io\Stream {

    use Event\Emitter\Aggregation;
    use Event\Loop\Aggregation;

    private $_host = '';
    private $_port = 0;
    private $_resource = NULL;
    private $_listener = NULL;

    public function __construct($host = '127.0.0.1', $port = 5333) {
      $this->_host = $host;
      $this->_port = (int)$port;
    }

    public function __destruct() {
      $this->close();
    }

    public function resource($resource = NULL) {
      if ($resource === FALSE) {
        $this->_resource = NULL;
      } elseif (isset($resource)) {
        $this->_resource = $resource;
        $that = $this;
        $this->_listener = $this->loop()->setInterval(
          function() use ($that) {
            $that->read();
          },
          100
        );
      }
      if (is_resource($this->_resource)) {
        return $this->_resource;
      } elseif (isset($this->_listener)) {
        $this->loop()->remove($this->_listener);
        $this->_listener = NULL;
      }
      return NULL;
    }

    public function open() {
      $resource = @stream_socket_client('tcp://'.$this->_host.':'.$this->_port, $no, $string, 2);
      if ($resource) {
        stream_set_blocking($resource, 0);
        stream_set_read_buffer($resource, 0);
        stream_set_write_buffer($resource, 0);
        stream_set_timeout($resource, 10000);
        $this->resource($resource);
        return TRUE;
      } else {
        $this
          ->events()
          ->emit(
            'error',
            sprintf('Can not open tcp server: "%s:%d".', $this->_host, $this->_port)
          );
        return FALSE;
      }
    }

    public function close() {
      if ($resource = $this->resource()) {
        $this->resource(FALSE);
        stream_socket_shutdown($resource, STREAM_SHUT_RDWR);
      }
    }

    public function read($bytes = 1024) {
      if ($resource = $this->resource()) {
        $data = stream_socket_recvfrom($resource, $bytes);
        if (is_string($data) && $data !== '') {
          $this->events()->emit('read-data', $data);
          return $data;
        }
      }
      return NULL;
    }

    public function write($data) {
      if ($resource = $this->resource()) {
        stream_socket_sendto(
          $resource,
          $writtenData = is_array($data) ? \Carica\Io\encodeBinaryFromArray($data) : $data
        );
        $this->events()->emit('write-data', $writtenData);
      }
    }
  }
}
