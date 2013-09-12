<?php

namespace Carica\Io\Event\Loop\Libevent\Listener {

  use Carica\Io\Event;
  use Carica\Io\Event\Loop\Libevent;

  class Interval extends Libevent\Listener {

    private $_milliseconds = 0;

    public function __construct(Event\Loop\Libevent $loop, Callable $callback, $milliseconds) {
      parent::__construct($loop, $callback);
      $this->_milliseconds = $milliseconds;
    }

    public function __invoke() {
      $this->_event = event_new();
      $that = $this;
      $period = $this->_milliseconds * 1000;
      $callback = function () use ($that, $period, &$callback) {
        if (!$that->isCancelled()) {
          call_user_func($that->getCallback());
          event_add($that->_event, $period);
        }
      };
      event_timer_set($this->_event, $callback);
      event_base_set($this->_event, $this->getLoop()->getBase());
      event_add($this->_event, $period);
      return $this;
    }
  }
}