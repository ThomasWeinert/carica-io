<?php

namespace Carica\Io\Event\Emitter\Listener {

  use Carica\Io\Event;

  /**
   * @property Event\Emitter $emitter
   * @property string $event
   * @property callable $callback
   */
  class Once extends On {

    public function __invoke() {
      $this->emitter->removeListener($this->event, $callback = $this->getCallback());
      call_user_func_array($callback, func_get_args());
    }
  }
}