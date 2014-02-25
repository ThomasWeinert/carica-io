<?php

namespace Carica\Io\Event {

  use \Carica\Io\Event;

  interface HasLoop {

    /**
     * @param Event\Loop $loop
     * @return Event\Loop
     */
    function loop(Event\Loop $loop = NULL);
  }
}