<?php
declare(strict_types=1);

namespace Carica\Io\Event {

  use Carica\Io\Event\Emitter as EventEmitter;

  interface HasEmitter {

    /**
     * @param EventEmitter $events
     * @return EventEmitter
     */
    public function events(EventEmitter $events = NULL): EventEmitter;
  }
}
