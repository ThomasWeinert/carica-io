<?php

namespace Carica\Io\Event\Loop\Listener {

  use Carica\Io\Event;
  use Carica\Io\Stream;

  class StreamReader extends Event\Loop\Listener {

    private $_stream = NULL;
    private $_callback = NULL;

    public function __construct(Event\Loop $loop, Callable $callback, $stream) {
      parent::__construct($loop);
      $this->_callback = $callback;
      $this->_stream = $stream;
    }

    public function tick() {
      call_user_func($this->_callback, $this->_stream);
    }

    public function getResource() {
      return $this->_stream;
    }
  }
}