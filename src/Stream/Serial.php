<?php

namespace Carica\Io\Stream {

  use Carica\Io;
  use Carica\Io\Event;
  use Carica\Io\Stream\Serial\Device;

  class Serial
    implements
      Io\Stream,
      Io\Event\HasLoop {

    use Event\Emitter\Aggregation;
    use Event\Loop\Aggregation;

    private $_device;
    private $_resource;
    private $_listener;

    /**
     * Serial constructor.
     * @param $device
     * @param int $baud
     * @throws \LogicException
     */
    public function __construct(string $device, int $baud = Device::BAUD_DEFAULT)
    {
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

    public function isOpen(): bool {
      return \is_resource($this->resource());
    }

    public function open(): bool {
      $this->_device->setUp();
      $device = (string)$this->_device;
      if ($resource = @\fopen($device, 'rb+')) {
        \stream_set_blocking($resource, 0);
        \stream_set_timeout($resource, 1);
        $this->resource($resource);
        return TRUE;
      } else {
        $this->events()->emit('error', sprintf('Can not open serial port: "%s".', $device));
        return FALSE;
      }
    }

    public function close() {
      if ($resource = $this->resource()) {
        fclose($resource);
      }
      $this->resource(FALSE);
    }

    public function read(int $bytes = 1024): ?string {
      if ($resource = $this->resource()) {
        $data = \fread($resource, $bytes);
        if (\is_string($data) && $data !== '') {
          $this->events()->emit('read-data', $data);
          return $data;
        }
      }
      return NULL;
    }

    public function write($data): bool {
      if ($resource = $this->resource()) {
        \fwrite(
          $resource,
          $writtenData = \is_array($data) ? Io\encodeBinaryFromArray($data) : $data
        );
        $this->events()->emit('write-data', $writtenData);
        return TRUE;
      }
      return FALSE;
    }
  }
}
