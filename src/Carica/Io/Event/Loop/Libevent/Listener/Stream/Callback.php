<?php

namespace Carica\Io\Event\Loop\Libevent\Listener\Stream {

  use Carica\Io;
  use Carica\Io\Event;
  use Carica\Io\Event\Loop\Libevent\Listener;

  class Callback {

    private $_listener = NULL;
    private $_remove = NULL;
    private $_callback = NULL;

    public function __construct(
      Listener\Stream $listener, Callable $remove, Callable $callback
    ) {
      $this->_listener = $listener;
      $this->_callback = $callback;
    }

    public function __invoke() {
      call_user_func($this->_callback);
    }

    public function remove() {
      call_user_func($this->_remove, $this);
    }

  }
}