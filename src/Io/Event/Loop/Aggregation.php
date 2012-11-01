<?php

namespace Carica\Io\Event\Loop {

  use Carica\Io\Event;

  trait Aggregation {

    /**
     * @var Carica\Io\Event\Loop
     */
    private $_eventLoop = NULL;

    /**
     * Getter/Setter for the event loop including implicit create. The create uses
     * the factory fetching a global instance of the loop by default.
     *
     * @param Carica\Io\Event\Loop $loop
     * @return Carica\Io\Event\Loop
     */
    public function loop(Event\Loop $loop = NULL) {
      if (NULL !== $loop) {
        $this->_eventLoop = $loop;
      } elseif (NULL === $this->_eventLoop) {
        $this->_eventLoop = Factory::get();
      }
      return $this->_eventLoop;
    }
  }
}