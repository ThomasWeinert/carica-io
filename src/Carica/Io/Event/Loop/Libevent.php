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
      $event = new stdClass();
      $event->resource = event_new();
      $this->events[spl_object_hash($event)] = $event;
      $that = $this;
      event_timer_set(
        $event,
        function () use ($that, $event, $callback) {
          $callback();
          $that->remove($event);
        }
      );
      event_base_set($event->resource, $this->_base);
      event_add($event->resource, $milliseconds * 1000);
      return $event;
    }

    public function setInterval(Callable $callback, $milliseconds) {
      $event = new stdClass();
      $event->resource = event_new();
      $this->events[spl_object_hash($event)] = $event;
      $period = $milliseconds * 1000;
      event_timer_set(
        $event,
        function () use ($event, $callback, $period) {
          $callback();
          event_add($event->resource, $period);
        }
      );
      event_base_set($event->resource, $this->_base);
      event_add($event->resource, $period);
      return $event;
    }

    public function setStreamReader(Callable $callback, $stream) {
    }

    public function remove($event) {
      $key = spl_object_hash($event);
      if (array_key_exists($key, $this->_events)) {
        event_free($this->_base, $this->_events[$key]);
        unset($this->_events[$key]);
      }
    }

    public function run() {
      event_base_loop($this->_base);
    }

    public function stop() {
      event_base_loopbreak($this->_base);
    }
  }
}