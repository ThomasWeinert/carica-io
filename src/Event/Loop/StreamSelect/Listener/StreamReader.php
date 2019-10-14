<?php
declare(strict_types=1);

namespace Carica\Io\Event\Loop\StreamSelect\Listener {

  use Carica\Io\Event;
  use Carica\Io\Event\Loop\StreamSelect;

  class StreamReader extends StreamSelect\Listener {

    private $_stream;
    private $_callback;

    public function __construct(Event\Loop $loop, callable $callback, $stream) {
      parent::__construct($loop);
      $this->_callback = $callback;
      $this->_stream = $stream;
    }

    public function tick(): bool {
      call_user_func($this->_callback, $this->_stream);
      return FALSE;
    }

    public function getResource() {
      return $this->_stream;
    }
  }
}
