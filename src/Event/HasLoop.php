<?php
declare(strict_types=1);

namespace Carica\Io\Event {

  use \Carica\Io\Event\Loop as EventLoop;

  interface HasLoop {

    /**
     * @param EventLoop $loop
     * @return EventLoop
     */
    public function loop(EventLoop $loop = NULL): EventLoop;
  }
}
