<?php

namespace Carica\Io\Stream {

  use Carica\Io\Event;

  class SerialPort {

    use Event\Emitter\Aggregation;
    use Event\Loop\Aggregation;

    private $_number = 0;
    private $_resource = NULL;
    private $_listener = NULL;

    private $_reading = FALSE;

    public function __construct($number) {
      $this->_number = $number;
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
      if (substr(PHP_OS, 0, 3) === "WIN") {
        $device = 'COM'.((int)$this->_number).':';
        $prepare = sprintf('mode com%d: BAUD=57600 PARITY=N data=8 stop=1 xon=off', $this->_number);
      } elseif (substr(PHP_OS, 0, 6) === "Darwin") {
        $device = 'COM'.((int)$this->_number).':';
        $prepare = sprintf('stty -F %s', $device);
      } elseif (substr(PHP_OS, 0, 5) === "Linux") {
        $device = '/dev/ttyS'.((int)$this->_number);
        $prepare = sprintf('stty -F %s', $device);
      } else {
        $this->events()->emit('error', sprintf('Unsupport OS: "%s".', PHP_OS));
        return FALSE;
      }
      exec($prepare);
      if ($resource = @fopen($device, 'rb+')) {
        stream_set_blocking($resource, 0);
        stream_set_timeout($resource, 1);
        $this->resource($resource);
        return TRUE;
      } else {
        $this->events()->emit('error', sprintf('Can not open serial port: "%d".', $this->_number));
        return FALSE;
      }
    }

    public function close() {
      if ($resource = $this->resource()) {
        $this->resource(FALSE);
        fclose($resource);
      }
    }

    public function read($bytes = 1024) {
      if ($this->_reading && ($resource = $this->resource())) {
        $data = fread($resource, $bytes);
        if (is_string($data) && $data !== '') {
          $this->events()->emit('read', $data);
          $this->events()->emit('data', $data);
          return $data;
        }
      }
      return NULL;
    }

    public function write($data) {
      if ($resource = $this->resource()) {
        if (is_array($data)) {
          array_unshift($data, 'C*');
          fwrite($resource, $binary = call_user_func_array('pack', $data));
          $this->events()->emit('write', $binary);
        } else {
          fwrite($resource, $data);
          $this->events()->emit('write', $data);
        }
        // according to a php bug report, reading is only possible after writing some stuff
        if (!$this->_reading) {
          usleep(1000);
          $this->_reading = TRUE;
        }
      }
    }
  }
}
