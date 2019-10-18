<?php
declare(strict_types=1);

namespace Carica\Io\Stream {

  use Carica\Io;
  use Carica\Io\Stream\Serial\Device;
  use Carica\Io\Event\Emitter as EventEmitter;
  use Carica\Io\Event\HasLoop as HasEventLoop;
  use Carica\Io\Event\Loop as EventLoop;
  use Carica\Io\Stream;

  class SerialStream
    implements
      Stream,
      HasEventLoop {

    use EventEmitter\Aggregation;
    use EventLoop\Aggregation;

    private $_device;
    private $_resource;
    private $_listener;

    /**
     * Serial constructor.
     *
     * @param EventLoop $loop
     * @param string $device
     * @param int $baud
     */
    public function __construct(EventLoop $loop, string $device, int $baud = Device::BAUD_DEFAULT) {
      $this->loop($loop);
      $this->_device = new Serial\Device($device, $baud);
    }

    public function __destruct() {
      $this->close();
    }

    public function resource($resource = NULL) {
      if ($resource === FALSE) {
        $this->_resource = NULL;
      } elseif (NULL !== $resource) {
        $this->_resource = $resource;
        $this->_listener = $this->loop()->setStreamReader(
          function() { $this->read(); }, $resource
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

    public function isOpen(): bool {
      return is_resource($this->resource());
    }

    public function open(): bool {
      $this->_device->setUp();
      $device = (string)$this->_device;
      if ($resource = @fopen($device, 'rb+')) {
        stream_set_blocking($resource, 0);
        stream_set_timeout($resource, 1);
        $this->resource($resource);
        return TRUE;
      }
      $this->events()->emit(self::EVENT_ERROR, sprintf('Can not open serial port: "%s".', $device));
      return FALSE;
    }

    public function close() {
      if ($resource = $this->resource()) {
        fclose($resource);
      }
      $this->resource(FALSE);
    }

    public function read(int $bytes = 1024): ?string {
      if ($resource = $this->resource()) {
        $data = fread($resource, $bytes);
        if (is_string($data) && $data !== '') {
          $this->events()->emit(self::EVENT_READ_DATA, $data);
          return $data;
        }
      }
      return NULL;
    }

    public function write($data): bool {
      if ($resource = $this->resource()) {
        fwrite(
          $resource,
          $writtenData = is_array($data) ? Io\encodeBinaryFromArray($data) : $data
        );
        $this->events()->emit(self::EVENT_WRITE_DATA, $writtenData);
        return TRUE;
      }
      return FALSE;
    }
  }
}
