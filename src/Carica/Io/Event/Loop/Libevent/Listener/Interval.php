<?php

namespace Carica\Io\Event\Loop\Libevent\Listener {

  use Carica\Io\Event;
  use Carica\Io\Event\Loop\Libevent;

  class Interval extends Libevent\Listener {

    private $_milliseconds = 0;

    public function __construct(Event\Loop $loop, Callable $callback, $milliseconds) {
      parent::__construct($loop);
      $this->_milliseconds = $milliseconds;
      $this->_callback = $callback;
    }

    public function __invoke() {
      $this->_event = event_new();
      $that = $this;
      $period = $this->_milliseconds * 1000;
      $callback = $this->_callback;
      event_timer_set(
        $this->_event,
        function () use ($that, $callback, $period) {
          $callback();
          event_add($that->_event, $period);
        }
      );
      event_base_set($this->_event, $this->getLoop()->getBase());
      event_add($this->_event, $period);
      return $this;
    }
  }
}