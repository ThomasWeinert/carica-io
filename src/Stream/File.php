<?php

namespace Carica\Io\Stream {

  use Carica\Io;
  use Carica\Io\Event;

  class File
    implements
      Io\Stream,
      Io\Event\HasLoop {

    use Event\Emitter\Aggregation;
    use Event\Loop\Aggregation;

    private $_filename = '';
    private $_mode = '';

    private $_resource = NULL;
    private $_listener = NULL;

    /**
     * Store filename and mode options
     *
     * @param string $filename
     * @param string $mode
     */
    public function __construct($filename, $mode = 'r') {
      $this->_filename = $filename;
      $this->_mode = $mode;
    }

    /**
     * On destruct close file resource
     */
    public function __destruct() {
      $this->close();
    }

    /**
     * Read/Write the file resource, if it is an valid resource attach
     * the an event listener to the loop, that calls read on new data
     *
     * @param string $resource
     * @return NULL|resource
     */
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

    public function isOpen()
    {
      return is_resource($this->resource());
    }

    /**
     * Open file (nonblocking) and store resource
     *
     * @return boolean
     */
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

    /**
     * Close file resource if it is opened
     */
    public function close() {
      if ($resource = $this->resource()) {
        fclose($resource);
      }
      $this->resource(FALSE);
    }

    /**
     * Read some bytes from the file resource
     *
     * @param integer $bytes
     * @return string|NULL
     */
    public function read($bytes = 1024) {
      if ($resource = $this->resource()) {
        $data = fread($resource, $bytes);
        if (is_string($data) && $data !== '') {
          $this->events()->emit('read-data', $data);
          return $data;
        }
      }
      return NULL;
    }

    /**
     * Write some bytes to the file resource
     *
     * @param string|array(integer) $data
     * @return bool
     */
    public function write($data) {
      if ($resource = $this->resource()) {
        fwrite(
          $resource,
          $writtenData = is_array($data) ? Io\encodeBinaryFromArray($data) : $data
        );
        $this->events()->emit('write-data', $writtenData);
        return TRUE;
      }
      return FALSE;
    }
  }
}
