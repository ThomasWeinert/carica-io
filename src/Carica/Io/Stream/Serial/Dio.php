<?php

namespace Carica\Io\Stream\Serial {

  use Carica\Io;
  use Carica\Io\Event;

  class Dio implements Io\Stream {

    use Event\Emitter\Aggregation;
    use Event\Loop\Aggregation;

    private $_device = 0;
    private $_command = '';
    private $_resource = NULL;
    private $_listener = NULL;

    private $_reading = FALSE;

    public function __construct($device, $baud = 57000) {
      $this->_device = new Device($device, $baud);
    }
    public function __destruct() {
      $this->close();
    }

    public function resource($resource = NULL) {
      if ($resource === FALSE) {
        $this->_resource = NULL;
      } elseif (isset($resource)) {
        $this->_resource = $resource;
        $that = $this;
        $this->_listener = $this->loop()->setInterval(
          function() use ($that) {
            $that->read();
          },
          50
        );
      }
      if (is_resource($this->_resource)) {
        return $this->_resource;
      } elseif (isset($this->_listener)) {
        $this->loop()->remove($this->_listener);
        $this->_listener = NULL;
      }
      return NULL;
    }

    public function open() {
      $this->_device->setUp();
      $device = (string)$this->_device;
      if ($resource = @dio_open($device, O_RDWR | O_NOCTTY | O_NONBLOCK)) {
        $this->resource($resource);
        return TRUE;
      } else {
        $this->events()->emit('error', sprintf('Can not open serial port: "%s".', $device));
        return FALSE;
      }
    }

    public function close() {
      if ($resource = $this->resource()) {
        $this->resource(FALSE);
        dio_close($resource);
      }
    }

    public function read($bytes = 1024) {
      if ($resource = $this->resource()) {
        $data = dio_read($resource, $bytes);
        if (is_string($data) && $data !== '') {
          $this->events()->emit('read-data', $data);
          return $data;
        }
      }
      return NULL;
    }

    public function write($data) {
      if ($resource = $this->resource()) {
        dio_write(
          $resource,
          $writtenData = is_array($data) ? Io\encodeBinaryFromArray($data) : $data
        );
        $this->events()->emit('write-data', $writtenData);
      }
    }
  }
}
