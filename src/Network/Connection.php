<?php
declare(strict_types=1);

namespace Carica\Io\Network {

  use Carica\Io;
  use Carica\Io\Event\HasEmitter as HasEventEmitter;
  use Carica\Io\Event\Emitter as EventEmitter;
  use Carica\Io\Event\HasLoop as HasEventLoop;
  use Carica\Io\Event\Loop as EventLoop;
  use Carica\Io\Event\Loop\Listener as EventLoopListener;

  class Connection implements HasEventEmitter, HasEventLoop {

    use EventEmitter\Aggregation;
    use EventLoop\Aggregation;

    public const EVENT_READ_DATA = 'read-data';
    public const EVENT_CLOSE = 'close';

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
          $this->_listener = $this->loop()->setStreamReader(
            function() { $this->read(); },
            $stream
          );
        }
      }
      return $this->_stream;
    }

    public function isActive() {
      return is_resource($this->_stream);
    }

    public function read($bytes = 65535) {
      if ($this->isActive()) {
        $data = stream_socket_recvfrom($this->_stream, $bytes);
        if (is_string($data) && $data !== '') {
          $this->events()->emit(self::EVENT_READ_DATA, $data);
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
        $this->events()->emit(self::EVENT_CLOSE);
      }
      $this->resource(FALSE);
    }
  }
}
