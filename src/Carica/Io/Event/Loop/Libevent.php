<?php

namespace Carica\Io\Event\Loop {

  use Carica\Io\Event;

  class Libevent implements Event\Loop {

    private $_base = NULL;
    private $_events = array();

    public function __construct($base) {
      $this->_base = $base;
    }

    public function setTimeout(Callable $callback, $milliseconds) {
      $this->events[] = $event = event_new();
      event_timer_set($event, $callback);
      event_base_set($event, $this->_base);
      event_add($event, $milliseconds * 1000);
      return $event;
    }

    public function setInterval(Callable $callback, $milliseconds) {
      $this->events[] = $event = event_new();
      $period = $milliseconds * 1000;
      event_timer_set(
        $event,
        function () use ($event, $callback, $period) {
          $callback();
          event_add($event, $period);
        }
      );
      event_base_set($event, $this->_base);
      event_add($event, $period);
      return $event;
    }

    public function setStreamReader(Callable $callback, $stream) {
    }

    public function remove($listener) {
    }

    public function run() {
      event_base_loop($this->_base);
    }

    public function stop() {
      event_base_loopbreak($this->_base);
    }
  }
}