<?php

namespace Carica\Io\Event\Loop {

  use Carica\Io\Event;

  abstract class Listener {

    private $_loop = NULL;

    abstract function tick();

    public function loop(Event\Loop $loop = NULL) {
      if (isset($loop)) {
        if (isset($this->_loop)) {
          $this->_loop->remove($this);
        }
        $this->_loop = $loop;
      }
      return $this->_loop;
    }
  }
}