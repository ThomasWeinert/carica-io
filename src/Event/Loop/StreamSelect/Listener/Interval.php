<?php

namespace Carica\Io\Event\Loop\StreamSelect\Listener {

  use Carica\Io\Event;
  use Carica\Io\Event\Loop\StreamSelect;

  class Interval extends StreamSelect\Listener {

    private $_interval = 0;
    private $_next = 0;

    public function __construct(Event\Loop $loop, Callable $callback, $milliseconds) {
      parent::__construct($loop);
      $this->_interval = $milliseconds;
      $this->_callback = $callback;
      $this->_next = $this->getNow() + $milliseconds;
    }

    public function tick() {
      $now = $this->getNow();
      if ($now >= $this->_next) {
        $this->reset();
        call_user_func($this->_callback);
        return TRUE;
      }
      return FALSE;
    }

    public function reset() {
      $this->_next = $this->getNow() + $this->_interval;
    }

    private function getNow() {
      $loop = $this->getLoop();
      if ($loop instanceOf Event\Loop\Clock) {
        return $loop->getNow();
      }
      return ceil(microtime(TRUE) * 1000);
    }
  }
}