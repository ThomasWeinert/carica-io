<?php

namespace Carica\Io\Event {

  use \Carica\Io\Event;

  interface HasLoop {

    /**
     * @param Event\Loop $loop
     * @return Event\Loop
     */
    function events(Event\Loop $loop = NULL);
  }
}