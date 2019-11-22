<?php
declare(strict_types=1);

namespace Carica\Io\Event\Loop\StreamSelect\Listener {

  use Carica\Io\Event;
  use Carica\Io\Event\Loop\StreamSelect;

  class StreamReader extends StreamSelect\Listener {

    private $_stream;
    private $_callback;

    /**
     * @param Event\Loop $loop
     * @param callable $callback
     * @param resource $stream
     */
    public function __construct(Event\Loop $loop, callable $callback, $stream) {
      parent::__construct($loop);
      $this->_callback = $callback;
      $this->_stream = $stream;
    }

    /**
     * @return bool
     */
    public function tick(): bool {
      $callback = $this->_callback;
      $callback($this->_stream);
      return FALSE;
    }

    /**
     * @return resource
     */
    public function getResource() {
      return $this->_stream;
    }
  }
}
