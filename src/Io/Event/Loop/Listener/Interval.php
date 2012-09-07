<?php

namespace Carica\Io\Event\Loop\Listener {

  use Carica\Io\Event;

  class Interval extends Event\Loop\Listener {

    private $_interval = 0;
    private $_next = 0;

    public function __construct($milliseconds, $callback) {
      $this->_interval = $milliseconds;
      $this->_callback = $callback;
      $this->_next = $this->getNow() + $milliseconds;
    }

    public function tick() {
      $now = $this->getNow();
      if ($now >= $this->_next) {
        $this->_next = $now + $this->_interval;
        call_user_func($this->_callback);
        return TRUE;
      }
      return FALSE;
    }

    private function getNow() {
      return ceil(microtime(TRUE) * 1000);
    }
  }
}