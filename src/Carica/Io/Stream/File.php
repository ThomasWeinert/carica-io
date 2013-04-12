<?php

namespace Carica\Io\Stream {

  use Carica\Io\Event;

  class File implements \Carica\Io\Stream {

    use Event\Emitter\Aggregation;
    use Event\Loop\Aggregation;

    private $_filename = '';
    private $_mode = '';

    private $_resource = NULL;
    private $_listener = NULL;

    public function __construct($filename, $mode = 'r') {
      $this->_filename = $filename;
      $this->_mode = $mode;
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
      if ($resource = @fopen($this->_filename, $this->_mode)) {
        stream_set_blocking($resource, 0);
        $this->resource($resource);
        return TRUE;
      } else {
        $this->events()->emit('error', sprintf('Can not open file: "%s".', $this->_filename));
        return FALSE;
      }
    }

    public function close() {
      if ($resource = $this->Resource()) {
        $this->resource(FALSE);
        fclose($resource);
      }
    }

    public function read($bytes = 1024) {
      if ($resource = $this->Resource()) {
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
          $writtenData = is_array($data) ? \Carica\Io\encodeBinaryFromArray($data) : $data
        );
        $this->events()->emit('write-data', $writtenData);
      }
    }
  }
}
