<?php

namespace Carica\Io\Network {

  use Carica\Io;

  class Connection {

    use Io\Event\Emitter\Aggregation;
    use Io\Event\Loop\Aggregation;

    private $_listener = NULL;

    private $_stream = FALSE;

    public function __construct($stream) {
      $this->resource($stream);
    }

    public function resource($stream = NULL) {
      if (isset($stream)) {
        if (isset($this->_listener)) {
          $this->loop()->remove($this->_listener);
        }
        $this->_stream = $stream;
        if ($this->isActive()) {
          stream_set_blocking($stream, 0);
          $that = $this;
          $this->loop()->setStreamReader(
            function() use ($that) {
              $that->read();
            },
            $stream
          );
        }
      }
      return $this->_stream;
    }

    public function isActive() {
      return is_resource($this->_stream);
    }

    public function read($bytes = 1024) {
      if ($this->isActive()) {
        $data = stream_socket_recvfrom($this->_stream, $bytes);
        if (is_string($data) && $data !== '') {
          $this->events()->emit('data', $data);
          return $data;
        }
      }
      return '';
    }

    public function write($data) {
      if ($data != '' && $this->isActive()) {
        if (-1 === @stream_socket_sendto($this->_stream, $data)) {
          $this->close();
        }
      }
    }

    public function close() {
      if ($this->isActive()) {
        stream_socket_shutdown($this->_stream, STREAM_SHUT_RDWR);
        $this->resource(FALSE);
        $this->events()->emit('close');
      }
    }
  }
}