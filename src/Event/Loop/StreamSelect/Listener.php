<?php

namespace Carica\Io\Event\Loop\StreamSelect {

  use Carica\Io\Event;

  abstract class Listener implements Event\Loop\Listener {

    /**
     * @var Event\Loop
     */
    private $_loop;

    abstract public function tick();

    public function __construct(Event\Loop $loop) {
      $this->_loop = $loop;
    }

    public function getLoop() {
      return $this->_loop;
    }
  }
}
