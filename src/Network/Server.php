<?php
declare(strict_types=1);

namespace Carica\Io\Network {

  use Carica\Io\Event\HasEmitter as HasEventEmitter;
  use Carica\Io\Event\Emitter as EventEmitter;
  use Carica\Io\Event\HasLoop as HasEventLoop;
  use Carica\Io\Event\Loop as EventLoop;

  class Server implements HasEventEmitter, HasEventLoop {

    use EventEmitter\Aggregation;
    use EventLoop\Aggregation;

    public const EVENT_LISTEN = 'listen';
    public const EVENT_CONNECTION = 'connection';

    private $_listener;

    private $_stream;

    private $_address;

    public function __construct(EventLoop $loop, string $address = 'tcp://0.0.0.0') {
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
          $this->loop()->setStreamReader(
            function() { $this->accept(); },
            $stream
          );
        }
      }
      return $this->_stream;
    }

    public function isActive(): bool {
      return $this->_stream !== NULL;
    }

    public function listen(int $port = 8080): bool {
      if (!$this->isActive()) {
        $stream = stream_socket_server($this->_address.':'.$port, $errorNumber, $errorString);
        if ($stream) {
          stream_set_blocking($stream, FALSE);
          $this->resource($stream);
          $this->events()->emit(self::EVENT_LISTEN, $this->_address.':'.$port);
          return TRUE;
        }
        return FALSE;
      }
      return TRUE;
    }

    public function close(): void {
      if ($this->isActive()) {
        stream_socket_shutdown($this->resource(), STREAM_SHUT_RDWR);
      }
      $this->resource(FALSE);
    }

    private function accept(): void {
      if ($this->isActive() && ($stream = @stream_socket_accept($this->resource(), 1, $peer))) {
        $this->events()->emit(self::EVENT_CONNECTION, $stream, $peer);
      }
    }
  }
}
