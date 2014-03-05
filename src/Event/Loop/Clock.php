<?php

namespace Carica\Io\Event\Loop {

  use Carica\Io;
  use Carica\Io\Event;
  use Carica\Io\Event\Loop\StreamSelect\Listener;

  class Clock implements Event\Loop {

    private $_running;

    private $_currentTime = 0;
    private $_resolution = 5;

    private $_timers = array();
    private $_streams = array();

    public function __construct($now = NULL, $resolution = 1) {
      $this->_currentTime = isset($now) ? (int)$now : ceil(microtime(TRUE) * 1000);
      $this->_resolution = (int)$resolution;
    }

    public function setTimeout(Callable $callback, $milliseconds) {
      $listener = new Listener\Timeout($this, $callback, $milliseconds);
      return $this->_timers[spl_object_hash($listener)] = $listener;
    }

    public function setInterval(Callable $callback, $milliseconds) {
      $listener = new Listener\Interval($this, $callback, $milliseconds);
      return $this->_timers[spl_object_hash($listener)] = $listener;
    }

    public function setStreamReader(Callable $callback, $stream) {
      $listener = new \stdClass();
      return $this->_streams[spl_object_hash($listener)] = $listener;
    }

    public function remove($listener) {
      if (is_object($listener)) {
        $key = spl_object_hash($listener);
        if (isset($this->_timers[$key])) {
          unset($this->_timers[$key]);
        }
        if (isset($this->_streams[$key])) {
          unset($this->_streams[$key]);
        }
      }
    }

    public function run(Io\Deferred\Promise $for = NULL) {
      $this->_running = TRUE;
    }

    public function stop() {
      $this->_running = FALSE;
    }

    public function tick($milliseconds = 1) {
      $stop = $this->_currentTime + $milliseconds;
      while ($this->_currentTime < $stop) {
        $this->_currentTime += $this->_resolution;
        /** @var Listener $listener */
        foreach ($this->_timers as $listener) {
          $listener->tick();
        }
      }
    }

    public function count() {
      return count($this->_timers) + count($this->_streams);
    }

    public function getNow() {
      return $this->_currentTime;
    }
  }
}