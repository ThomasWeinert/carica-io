<?php

namespace Carica\Io\Stream {

  use Carica\Io;
  use Carica\Io\Event;

  class Serial implements Io\Stream {

    use Event\Emitter\Aggregation;
    use Event\Loop\Aggregation;

    private $_device = 0;
    private $_command = '';
    private $_resource = NULL;
    private $_listener = NULL;

    private $_reading = FALSE;

    public function __construct($device, $baud = 57000) {
      $this->_device = new Serial\Device($device, $baud);
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
        $this->_listener = $this->loop()->setStreamReader(
          function() use ($that) {
            $that->read();
          },
          $resource
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
      if ($resource = @fopen($device, 'rb+')) {
        stream_set_blocking($resource, 0);
        stream_set_timeout($resource, 1);
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

    public function read($bytes = 1024) {
      if ($this->_reading && ($resource = $this->resource())) {
        $data = fread($resource, $bytes);
        if (is_string($data) && $data !== '') {
          $this->events()->emit('read-data', $data);
          return $data;
        }
      }
      return NULL;
    }

    public function write($data) {
      if ($resource = $this->resource()) {
        fwrite(
          $resource,
          $writtenData = is_array($data) ? Io\encodeBinaryFromArray($data) : $data
        );
        $this->events()->emit('write-data', $writtenData);
        // according to a php bug report, reading is only possible after writing some stuff
        if (!$this->_reading) {
          usleep(1000);
          $this->_reading = TRUE;
        }
      }
    }
  }
}
