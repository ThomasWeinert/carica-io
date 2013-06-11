<?php

namespace Carica\Io\Event\Loop {

  use Carica\Io\Event;

  class Libevent implements Event\Loop {

    private $_base = NULL;
    private $_events = array();
    private $_resources = array();

    public function __construct($base) {
      $this->_base = $base;
    }

    public function setTimeout(Callable $callback, $milliseconds) {
      $event = new Libevent\Listener\Timeout($this, $callback, $milliseconds);
      $this->events[spl_object_hash($event)] = $event();
      return $event;
    }

    public function setInterval(Callable $callback, $milliseconds) {
      $event = new Libevent\Listener\Interval($this, $callback, $milliseconds);
      $this->events[spl_object_hash($event)] = $event();
      return $event;
    }

    public function setStreamReader(Callable $callback, $stream) {
    }

    public function remove($event) {
      $key = spl_object_hash($event);
      if (array_key_exists($key, $this->_events)) {
        unset($this->_events[$key]);
      }
    }

    public function run(\Carica\Io\Deferred\Promise $for = NULL) {
      if (isset($for) &&
          $for->state() === \Carica\Io\Deferred::STATE_PENDING) {
        $loop = $this;
        $for->always(
          function () use ($loop) {
            $loop->stop();
          }
        );
      }
      event_base_loop($this->_base);
    }

    public function stop() {
      event_base_loopbreak($this->_base);
    }

    public function getBase() {
      return $this->_base;
    }
  }
}