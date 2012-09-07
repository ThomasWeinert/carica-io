<?php

namespace Carica\Io\Event\Emitter {

  use Carica\Io\Event;

  trait Aggregation {

    private $_eventEmitter = NULL;

    public function eventEmitter(Event\Emitter $emitter = NULL) {
      if (NULL !== $emitter) {
        $this->_eventEmitter = $emitter;
      } elseif (NULL === $this->_eventEmitter) {
        $this->_eventEmitter = new Event\Emitter();
      }
      return $this->_eventEmitter;
    }
  }
}