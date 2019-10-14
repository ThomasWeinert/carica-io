<?php
declare(strict_types=1);

namespace Carica\Io\Network {

  use Carica\Io;
  use Carica\Io\Event\Loop as EventLoop;
  use Carica\Io\Event\Loop\Listener as EventLoopListener;

  class Connection
    implements
      Io\Event\HasEmitter,
      Io\Event\HasLoop {

    use Io\Event\Emitter\Aggregation;
    use Io\Event\Loop\Aggregation;

    /**
     * @var EventLoopListener
     */
    private $_listener;

    /**
     * @var bool|resource
     */
    private $_stream = FALSE;

    /**
     * @param EventLoop $loop
     * @param Io\Stream $stream
     */
    public function __construct(EventLoop $loop, $stream) {
      $this->loop($loop);
      $this->resource($stream);
    }
    public function __destruct() {
      $this->resource(FALSE);
    }

    public function resource($stream = NULL) {
      if (isset($stream)) {
        if (isset($this->_listener)) {
          $this->loop()->remove($this->_listener);
        }
        $this->_stream = $stream;
        if ($this->isActive()) {
          stream_set_blocking($stream, FALSE);
          $that = $this;
          $this->_listener = $this->loop()->setStreamReader(
            static function() use ($that) {
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
          $this->events()->emit('read-data', $data);
          return $data;
        }
      }
      return '';
    }

    public function write($data) {
      if (
        (string)$data !== '' &&
        $this->isActive() &&
        (-1 === @stream_socket_sendto($this->_stream, $data))
      ) {
        $this->close();
      }
    }

    public function close() {
      if ($this->isActive()) {
        stream_socket_shutdown($this->_stream, STREAM_SHUT_RDWR);
        $this->events()->emit('close');
      }
      $this->resource(FALSE);
    }
  }
}
