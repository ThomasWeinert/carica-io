<?php

namespace Carica\Io\Network {

  use Carica\Io;

  class Connection implements Io\Stream\Readable {

    use Io\Event\Emitter\Aggregation;

    private $_loop = NULL;
    private $_listener = NULL;

    private $_stream = FALSE;

    public function __construct(Io\Event\Loop $loop, $stream) {
      $this->_loop = $loop;
      $this->Resource($stream);
    }

    public function Resource($stream = NULL) {
      if (isset($stream)) {
        if (isset($this->_listener)) {
          $this->_loop->remove($this->_listener);
        }
        $this->_stream = $stream;
        if ($this->isActive()) {
          stream_set_blocking($stream, 0);
          $this->_loop->add(
            $this->_listener = new Io\Event\Loop\Listener\StreamReader($this)
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
        $this->Resource(FALSE);
        $this->events()->emit('close');
      }
    }
  }
}