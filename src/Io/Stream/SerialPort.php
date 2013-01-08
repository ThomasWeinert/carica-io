<?php

namespace Carica\Io\Stream {

  use Carica\Io\Event;

  class SerialPort implements Readable, Writeable {

    use Event\Emitter\Aggregation;

    private $_number = 0;
    private $_resource = NULL;
    private $_listener = NULL;

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
        $this->loop()->add(
          $this->_listener = new Event\Loop\Listener\StreamReader($this)
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
      if (substr(PHP_OS, 0, 7) === "Windows") {
        $device = 'COM'.((int)$this->_number);
        $prepare = sprintf('mode com%d: BAUD=9600 PARITY=N data=8 stop=1 xon=off', $this->_number);
      } elseif (substr(PHP_OS, 0, 6) === "Darwin") {
        $device = 'COM'.((int)$this->_number);
        $prepare = sprintf('stty -F %s', $device);
      } elseif (substr(PHP_OS, 0, 5) === "Linux") {
        $device = '/dev/ttyS'.((int)$this->_number);
        $prepare = sprintf('stty -F %s', $device);
      } else {
        $this->events()->emit('error', sprintf('Unsupport OS: "%a".', PHP_OS));
        return FALSE;
      }
      exec($prepare);
      if ($resource = @fopen($device, 'rwb')) {
        stream_set_blocking($resource, 0);
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
      if ($resource = $this->resource()) {
        $data = fread($resource, $bytes);
        if (is_string($data) && $data !== '') {
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
          fwrite($resource, call_user_func_array('pack', $data));
        } else {
          fwrite($resource, $data);
        }
      }
    }
  }
}
