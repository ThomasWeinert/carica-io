<?php

namespace Carica\Io\Event\Loop {

  use Carica\Io;
  use Carica\Io\Event\Loop\Listener;

  class Clock implements Io\Event\Loop {

    /**
     * @var bool
     */
    private $_running;

    /**
     * @var int
     */
    private $_currentTime;
    /**
     * @var int
     */
    private $_resolution;

    /**
     * @var array
     */
    private $_timers = [];
    /**
     * @var array
     */
    private $_streams = [];

    public function __construct(int $now = NULL, int $resolution = 1) {
      $this->_currentTime = (int)($now ?? ceil(microtime(TRUE) * 1000));
      $this->_resolution = $resolution;
    }

    public function setTimeout(Callable $callback, int $milliseconds): Listener {
      $listener = new StreamSelect\Listener\Timeout($this, $callback, $milliseconds);
      return $this->_timers[spl_object_hash($listener)] = $listener;
    }

    public function setInterval(Callable $callback, int $milliseconds): Listener {
      $listener = new StreamSelect\Listener\Interval($this, $callback, $milliseconds);
      return $this->_timers[spl_object_hash($listener)] = $listener;
    }

    public function setStreamReader(Callable $callback, $stream): Listener {
      $listener = new StreamSelect\Listener\StreamReader($this, function() {}, $stream);
      return $this->_streams[spl_object_hash($listener)] = $listener;
    }

    public function remove(Listener $listener): void {
      $key = spl_object_hash($listener);
      if (isset($this->_timers[$key])) {
        unset($this->_timers[$key]);
      }
      if (isset($this->_streams[$key])) {
        unset($this->_streams[$key]);
      }
    }

    public function run(Io\Deferred\Promise $for = NULL): void {
      $this->_running = TRUE;
    }

    public function stop(): void {
      $this->_running = FALSE;
    }

    public function tick($milliseconds = 1): void {
      $stop = $this->_currentTime + $milliseconds;
      while ($this->_currentTime < $stop) {
        $this->_currentTime += $this->_resolution;
        /** @var Listener $listener */
        foreach ($this->_timers as $listener) {
          $listener->tick();
        }
      }
    }

    public function count(): int {
      return \count($this->_timers) + \count($this->_streams);
    }

    public function getNow(): int {
      return $this->_currentTime;
    }
  }
}
