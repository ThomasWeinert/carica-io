<?php

namespace Carica\Io\Stream {

  use Carica\Io;
  use Carica\Io\Event\Emitter as EventEmitter;
  use Carica\Io\Event\HasLoop as HasEventLoop;
  use Carica\Io\Event\Loop as EventLoop;
  use Carica\Io\Stream;

  class FileStream
    implements
      Stream,
      HasEventLoop {

    use EventEmitter\Aggregation;
    use EventLoop\Aggregation;

    private $_filename;
    private $_mode;

    private $_resource;
    private $_listener;

    /**
     * Store filename and mode options
     *
     * @param EventLoop $loop
     * @param string $filename
     * @param string $mode
     */
    public function __construct(EventLoop $loop, $filename, $mode = 'rb') {
      $this->loop($loop);
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
     * @param resource|FALSE $resource
     * @return NULL|resource
     */
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

    /**
     * Open file (non-blocking) and store resource
     *
     * @return bool
     */
    public function open(): bool {
      if ($resource = @fopen($this->_filename, $this->_mode)) {
        stream_set_blocking($resource, 0);
        $this->resource($resource);
        return TRUE;
      }
      $this->events()->emit(self::EVENT_ERROR, sprintf('Can not open file: "%s".', $this->_filename));
      return FALSE;
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

    /**
     * Write some bytes to the file resource
     *
     * @param string|array(integer) $data
     * @return bool
     */
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
