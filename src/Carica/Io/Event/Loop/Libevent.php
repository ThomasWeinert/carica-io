<?php

namespace Carica\Io\Event\Loop {

  use Carica\Io\Event;

  class Libevent implements Event\Loop {

    private $_base = NULL;
    private $_timers = array();
    private $_streams = array();

    public function __construct($base) {
      $this->_base = $base;
    }

    public function setTimeout(Callable $callback, $milliseconds) {
      $event = new Libevent\Listener\Timeout($this, $callback, $milliseconds);
      $this->_timers[spl_object_hash($event)] = $event();
      return $event;
    }

    public function setInterval(Callable $callback, $milliseconds) {
      $event = new Libevent\Listener\Interval($this, $callback, $milliseconds);
      $this->_timers[spl_object_hash($event)] = $event();
      return $event;
    }

    public function setStreamReader(Callable $callback, $stream) {
      if (!isset($this->_streams[$stream])) {
        $this->_streams[$stream] = new Libevent\Listener\Stream($this, $stream);
      }
      $result = $this->_streams[$stream]->onRead($callback);
      return $result;
    }

    public function remove($event) {
      $key = spl_object_hash($event);
      if (array_key_exists($key, $this->_timers)) {
        unset($this->_timers[$key]);
      }
      if ($event instanceOf Listener\Stream\Callback) {
        $event->remove();
      } elseif ($event instanceOf Listener\Stream &&
                array_key_exists($stream = $event->getStream(), $this->_streams)) {
        unset($this->_streams[$stream]);
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