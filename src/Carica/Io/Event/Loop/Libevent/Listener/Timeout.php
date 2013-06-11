<?php

namespace Carica\Io\Event\Loop\Libevent\Listener {

  use Carica\Io\Event;
  use Carica\Io\Event\Loop\Libevent;

  class Timeout extends Libevent\Listener {

    private $_milliseconds = 0;

    public function __construct(Event\Loop $loop, Callable $callback, $milliseconds) {
      parent::__construct($loop);
      $this->_milliseconds = $milliseconds;
      $this->_callback = $callback;
    }

    public function __invoke() {
      $this->_event = event_new();
      $that = $this;
      $callback = $this->_callback;
      event_timer_set(
        $this->_event,
        function () use ($that, $callback) {
          $callback();
          $that->getLoop()->remove($that);
        }
      );
      event_base_set($this->_event, $this->getLoop()->getBase());
      event_add($this->_event, $this->_milliseconds * 1000);
      return $this;
    }
  }
}