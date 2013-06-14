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
      if (!is_resource($stream)) {
        throw new LogicException('%s needs a valid stream resource.', __METHOD__);
      }
      if (!isset($this->_streams[$stream])) {
        $this->_streams[$stream] = new Libevent\Listener\Stream($this, $stream);
      }
      $result = $this->_streams[$stream]->onRead($callback);
      return $result;
    }

    public function remove($event) {
      if (isset($event)) {
        $key = spl_object_hash($event);
        if (array_key_exists($key, $this->_timers)) {
          $listener = $this->_timers[$key];
          $listener->cancel();
          unset($this->_timers[$key]);
        }
        if ($event instanceOf Libevent\Listener\Stream\Callback) {
          $event->remove();
        } elseif ($event instanceOf Libevent\Listener\Stream &&
                  ($stream = $event->getStream())) {
          if (is_resource($stream) && isset($this->_streams[$stream])) {
            $listener = $this->_streams[$stream];
            $listener->cancel();
            unset($this->_streams[$stream]);
          }
        }
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
      if (isset($for) &&
          $for->state() !== \Carica\Io\Deferred::STATE_PENDING) {
        event_base_loop($this->_base, EVLOOP_ONCE);
      } else {
        event_base_loop($this->_base);
      }
    }

    public function stop() {
      event_base_loopbreak($this->_base);
    }

    public function getBase() {
      return $this->_base;
    }

    public function count() {
      return count($this->_timers) + count($this->_streams);
    }
  }
}