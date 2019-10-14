<?php
declare(strict_types=1);

namespace Carica\Io\Event {

  use \Carica\Io\Event;

  interface HasEmitter {

    /**
     * @param Event\Emitter $events
     * @return Event\Emitter
     */
    function events(Event\Emitter $events = NULL);
  }
}
