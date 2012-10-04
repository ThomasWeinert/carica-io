<?php

namespace Carica\Io\Network {

  use Carica\Io;

  class Server implements Io\Stream\Readable {

    use Io\Event\Emitter\Aggregation;

    private $_loop = NULL;
    private $_listener = NULL;

    private $_stream = FALSE;

    private $_address = 'tcp://0.0.0.0';

    public function __construct(Io\Event\Loop $loop, $address = 'tcp://0.0.0.0') {
      $this->_loop = $loop;
      $this->_address = $address;
    }

    public function __destruct() {
      $this->close();
    }

    public function Resource($stream = NULL) {
      if (isset($stream)) {
        $this->_stream = $stream;
        if ($this->isActive() && !isset($this->_listener)) {
          $this->_loop->add(
            $this->_listener = new Io\Event\Loop\Listener\StreamReader($this)
          );
        } elseif (isset($this->_listener)) {
          $this->_loop->remove($this->_listener);
        }
      }
      return $this->_stream;
    }

    public function isActive() {
      return is_resource($this->_stream);
    }

    public function listen($port = 8080) {
      if (!$this->isActive()) {
        $stream = stream_socket_server($this->_address.':'.$port, $errorNumber, $errorString);
        if ($stream) {
          stream_set_blocking($stream, 0);
          $this->Resource($stream);
          $this->events()->emit('listen', $this->_address.':'.$port);
        }
      }
    }

    public function close() {
      if ($this->isActive()) {
        stream_socket_shutdown($this->_stream, STREAM_SHUT_RDWR);
        $this->Resource(FALSE);
      }
    }

    public function read($bytes = 0) {
      if ($this->isActive() && ($stream = @stream_socket_accept($this->_stream, 1, $peer))) {
        $this->events()->emit('connection', $stream, $peer);
      }
    }
  }
}
