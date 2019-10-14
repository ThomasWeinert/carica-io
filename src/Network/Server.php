<?php

namespace Carica\Io\Network {

  use Carica\Io;

  class Server
    implements
      Io\Event\HasEmitter,
      Io\Event\HasLoop {

    use Io\Event\Emitter\Aggregation;
    use Io\Event\Loop\Aggregation;

    private $_listener;

    private $_stream;

    private $_address;

    public function __construct(Io\Event\Loop $loop, $address = 'tcp://0.0.0.0') {
      $this->loop($loop);
      $this->_address = $address;
    }

    public function __destruct() {
      $this->close();
    }

    /**
     * @param null $stream
     * @return resource|null
     */
    public function resource($stream = NULL) {
      if (NULL !== $stream && is_resource($stream)) {
        $this->_stream = $stream;
        if (NULL !== $this->_listener) {
          $this->loop()->remove($this->_listener);
        }
        if ($this->isActive()) {
          $that = $this;
          $this->loop()->setStreamReader(
            static function() use ($that) {
              $that->accept();
            },
            $stream
          );
        }
      }
      return $this->_stream;
    }

    public function isActive() {
      return $this->_stream !== NULL;
    }

    public function listen($port = 8080) {
      if (!$this->isActive()) {
        $stream = stream_socket_server($this->_address.':'.$port, $errorNumber, $errorString);
        if ($stream) {
          stream_set_blocking($stream, 0);
          $this->resource($stream);
          $this->events()->emit('listen', $this->_address.':'.$port);
          return TRUE;
        }
        return FALSE;
      }
      return TRUE;
    }

    public function close() {
      if ($this->isActive()) {
        stream_socket_shutdown($this->resource(), STREAM_SHUT_RDWR);
      }
      $this->resource(FALSE);
    }

    public function accept() {
      if ($this->isActive() && ($stream = @stream_socket_accept($this->resource(), 1, $peer))) {
        $this->events()->emit('connection', $stream, $peer);
      }
    }
  }
}
