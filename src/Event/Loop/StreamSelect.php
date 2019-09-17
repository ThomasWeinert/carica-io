<?php

namespace Carica\Io\Event\Loop {

  use Carica\Io;
  use Carica\Io\Event;
  use Carica\Io\Event\Loop\Listener;

  class StreamSelect implements Event\Loop {

    private $_running;
    private $_wait = 5;

    private $_timers = array();
    private $_streams = array();
    private $_resources = array(
      'read' => array(),
      'write' => array(),
      'except' => array()
    );
    private $_hasResources = FALSE;

    public function setTimeout(Callable $callback, int $milliseconds): \Carica\Io\Event\Loop\Listener {
      $listener = new StreamSelect\Listener\Timeout($this, $callback, $milliseconds);
      return $this->_timers[spl_object_hash($listener)] = $listener;
    }

    public function setInterval(Callable $callback, int $milliseconds): \Carica\Io\Event\Loop\Listener {
      $listener = new StreamSelect\Listener\Interval($this, $callback, $milliseconds);
      return $this->_timers[spl_object_hash($listener)] = $listener;
    }

    public function setStreamReader(Callable $callback, $stream): \Carica\Io\Event\Loop\Listener {
      $listener = new StreamSelect\Listener\StreamReader($this, $callback, $stream);
      $this->_streams[spl_object_hash($listener)] = $listener;
      $this->_resources['read'][spl_object_hash($listener)] = $stream;
      $this->updateStreamStatus();
      return $this->_streams[spl_object_hash($listener)] = $listener;
    }

    public function remove(\Carica\Io\Event\Loop\Listener $listener): void {
      $key = spl_object_hash($listener);
      if (isset($this->_timers[$key])) {
        unset($this->_timers[$key]);
      }
      if (isset($this->_streams[$key])) {
        unset($this->_streams[$key]);
      }
      foreach ($this->_resources as &$group) {
        if (isset($group[$key])) {
          unset($group[$key]);
        }
      }
      $this->updateStreamStatus();
    }

    /**
     * Start the loop, if a promise is provided, start the loop only if it
     * it is still pending and add a callback to stop the loop if is is
     * finished.
     *
     * @param Io\Deferred\Promise $for
     */
    public function run(Io\Deferred\Promise $for = NULL): void {
      $this->_running = TRUE;
      if (isset($for) &&
          $for->state() === Io\Deferred::STATE_PENDING) {
        $loop = $this;
        $for->always(
          function () use ($loop) {
            $loop->stop();
          }
        );
      }
      while ($this->tick()) {
        // ticking
      }
    }

    public function stop(): void {
      $this->_running = FALSE;
    }

    private function tick() {
      if ($this->_running) {
        /**
         * @var StreamSelect\Listener $listener
         */
        if ($this->_hasResources) {
          $read = $this->_resources['read'];
          $write = $this->_resources['write'];
          $except = $this->_resources['except'];
          stream_select($read, $write, $except, $this->_wait);
          foreach ($read as $key => $resource) {
            if (isset($this->_streams[$key]) && ($listener = $this->_streams[$key])) {
              $listener->tick();
            }
          }
          foreach ($this->_timers as $listener) {
            $listener->tick();
          }
        } else {
          usleep($this->_wait);
          foreach ($this->_timers as $listener) {
            $listener->tick();
          }
        }
        return TRUE;
      }
      return FALSE;
    }

    private function updateStreamStatus() {
      $this->_hasResources = (
        count($this->_resources['read']) > 0 ||
        count($this->_resources['write']) > 0 ||
        count($this->_resources['except']) > 0
      );
    }

    public function count() {
      return count($this->_timers) + count($this->_streams);
    }
  }
}
