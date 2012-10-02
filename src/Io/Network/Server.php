<?php

namespace Carica\Io\Network {

  use Carica\Io;

  class Server {

    use Io\Event\Emitter\Aggregation;

    private $_loop = NULL;
    private $_listener = NULL;

    private $_stream = FALSE;

    public function __construct(Io\Event\Loop $loop) {
      $this->_loop = $loop;
    }

    public function __destruct() {
      $this->close();
    }

    public function setStream($stream) {
      $this->_stream = $stream;
      if ($this->isActive() && !isset($this->_listener)) {
        $this->_loop->add(
          $this->_listener = new Io\Event\Loop\Listener\Interval(
            50,
            array($this, 'accept')
          )
        );
      } elseif (isset($this->_listener)) {
        $this->_loop->remove($this->_listener);
      }
    }

    public function isActive() {
      return is_resource($this->_stream);
    }

    public function listen($address = 8080) {
      if (!$this->isActive()) {
        $stream = stream_socket_server($address, $errorNumber, $errorString);
        if ($stream) {
          stream_set_blocking($stream, 0);
          $this->setStream($stream);
          $this->eventEmitter()->emit('listen', $address);
        }
      }
    }

    public function close() {
      if ($this->isActive()) {
        stream_socket_shutdown($clientStream, STREAM_SHUT_RDWR);
        $this->setStream(FALSE);
      }
    }

    public function accept() {
      if ($this->isActive() && ($stream = @stream_socket_accept($this->_stream, 1, $peer))) {
        $this->eventEmitter()->emit('connection', $stream, $peer);
      }
    }
  }
}
