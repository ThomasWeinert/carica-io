<?php

namespace Carica\Io\Event\Loop\StreamSelect {

  use Carica\Io\Event;

  abstract class Listener {

    private $_loop = NULL;

    abstract function tick();

    public function __construct(Event\Loop $loop) {
      $this->_loop = $loop;
    }

    public function getLoop() {
      return $this->_loop;
    }
  }
}