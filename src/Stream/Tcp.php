<?php

namespace Carica\Io\Stream {

  use Carica\Io;
  use Carica\Io\Event;

  class Tcp
    implements
      Io\Stream,
      Io\Event\HasLoop {

    use Event\Emitter\Aggregation;
    use Event\Loop\Aggregation;

    private $_host;
    private $_port;
    private $_resource;
    private $_listener;

    public function __construct(string $host = '127.0.0.1', int $port = 5333) {
      $this->_host = $host;
      $this->_port = (int)$port;
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
        $that = $this;
        $this->_listener = $this->loop()->setStreamReader(
          function() use ($that) {
            $that->read();
          },
          $resource
        );
      }
      if (\is_resource($this->_resource)) {
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
      return \is_resource($this->resource());
    }

    public function open(): bool {
      $resource = @\stream_socket_client(
        'tcp://'.$this->_host.':'.$this->_port, $no, $string, 2
      );
      if ($resource) {
        \stream_set_blocking($resource, 0);
        \stream_set_read_buffer($resource, 0);
        \stream_set_write_buffer($resource, 0);
        \stream_set_timeout($resource, 10000);
        $this->resource($resource);
        return TRUE;
      }
      $this
        ->events()
        ->emit(
          'error',
          sprintf('Can not open tcp server: "%s:%d".', $this->_host, $this->_port)
        );
      return FALSE;
    }

    public function close() {
      if ($resource = $this->resource()) {
        $this->resource(FALSE);
        stream_socket_shutdown($resource, STREAM_SHUT_RDWR);
      } else {
        $this->resource(FALSE);
      }
    }

    public function read(int $bytes = 1024): ?string {
      if ($resource = $this->resource()) {
        $data = \stream_socket_recvfrom($resource, $bytes);
        if (\is_string($data) && $data !== '') {
          $this->events()->emit('read-data', $data);
          return $data;
        }
      }
      return NULL;
    }

    public function write($data): bool {
      if ($resource = $this->resource()) {
        $bytesSent = @stream_socket_sendto(
          $resource,
          $writtenData = \is_array($data) ? Io\encodeBinaryFromArray($data) : $data
        );
        if ($bytesSent !== -1) {
          $this->events()->emit('write-data', $writtenData);
          return TRUE;
        }
        $this->events()->emit(
          'error', 'Socket sent to failed.'
        );
      }
      return FALSE;
    }
  }
}
