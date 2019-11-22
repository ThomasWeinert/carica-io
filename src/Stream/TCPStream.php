<?php
declare(strict_types=1);

namespace Carica\Io\Stream {

  use Carica\Io;
  use Carica\Io\Event\Emitter as EventEmitter;
  use Carica\Io\Event\HasLoop as HasEventLoop;
  use Carica\Io\Event\Loop as EventLoop;
  use Carica\Io\Stream;

  class TCPStream
    implements
      Stream,
      HasEventLoop {

    use EventEmitter\Aggregation;
    use EventLoop\Aggregation;

    private $_host;
    private $_port;
    private $_resource;
    private $_listener;

    public function __construct(EventLoop $loop, string $host = '127.0.0.1', int $port = 5333) {
      $this->loop($loop);
      $this->_host = $host;
      $this->_port = $port;
    }

    public function __destruct() {
      $this->close();
    }

    public function resource($resource = NULL) {
      if ($resource === FALSE) {
        $this->_resource = NULL;
      }
      if (NULL !== $resource) {
        $this->_resource = $resource;
        $this->_listener = $this->loop()->setStreamReader(
          function(){
            $this->read();
          },
          $resource
        );
      }
      if (is_resource($this->_resource)) {
        return $this->_resource;
      }
      if (NULL !== $this->_listener) {
        $this->loop()->remove($this->_listener);
        $this->_listener = NULL;
      }
      return NULL;
    }

    public function isOpen(): bool
    {
      return is_resource($this->resource());
    }

    public function open(): bool {
      $resource = @stream_socket_client(
        'tcp://'.$this->_host.':'.$this->_port, $no, $string, 2
      );
      if ($resource) {
        stream_set_blocking($resource, 0);
        stream_set_read_buffer($resource, 0);
        stream_set_write_buffer($resource, 0);
        stream_set_timeout($resource, 10000);
        $this->resource($resource);
        return TRUE;
      }
      $this
        ->events()
        ->emit(
          self::EVENT_ERROR,
          sprintf('Can not open tcp server: "%s:%d".', $this->_host, $this->_port)
        );
      return FALSE;
    }

    public function close(): void {
      if ($resource = $this->resource()) {
        $this->resource(FALSE);
        stream_socket_shutdown($resource, STREAM_SHUT_RDWR);
      } else {
        $this->resource(FALSE);
      }
    }

    public function read(int $bytes = 1024): ?string {
      if ($resource = $this->resource()) {
        $data = stream_socket_recvfrom($resource, $bytes);
        if (is_string($data) && $data !== '') {
          $this->events()->emit(self::EVENT_READ_DATA, $data);
          return $data;
        }
      }
      return NULL;
    }

    /**
     * @param int[]|string $data
     * @return bool
     */
    public function write($data): bool {
      if ($resource = $this->resource()) {
        $bytesSent = @stream_socket_sendto(
          $resource,
          $writtenData = is_array($data) ? Io\encodeBinaryFromArray($data) : $data
        );
        if ($bytesSent !== -1) {
          $this->events()->emit(self::EVENT_WRITE_DATA, $writtenData);
          return TRUE;
        }
        $this->events()->emit(
          self::EVENT_ERROR, 'Socket sent to failed.'
        );
      }
      return FALSE;
    }
  }
}
