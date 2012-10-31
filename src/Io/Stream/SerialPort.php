<?php

namespace Carica\Io\Stream {

  use Carica\Io\Event;

  class SerialPort implements Readable, Writeable {

    use Event\Emitter\Aggregation;

    private $_address = '';
    private $_resource = NULL;
    private $_listener = NULL;

    public function __construct($address) {
      $this->_address = $address;
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
      if ($resource = @fopen($this->_address, 'rwb')) {
        stream_set_blocking($resource, 0);
        $this->resource($resource);
        return TRUE;
      } else {
        $this->events()->emit('error', sprintf('Can not open port: "%s".', $this->_address));
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
      if ($resource = $this->Resource()) {
        $data = fread($resource, $bytes);
        if (is_string($data) && $data !== '') {
          $this->events()->emit('data', $data);
          return $data;
        }
      }
      return NULL;
    }

    public function write($data) {
      if ($resource = $this->Resource()) {
        if (is_array($data)) {
          foreach ($data as $byte) {
            fwrite($resource, $byte);
          }
        } else {
          fwrite($resource, $data);
        }
      }
    }
  }
}
