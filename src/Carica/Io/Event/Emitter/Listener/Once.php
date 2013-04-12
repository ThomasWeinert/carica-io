<?php

namespace Carica\Io\Event\Emitter\Listener {

  use Carica\Io\Event;

  class Once extends On {

    public function __invoke() {
      $this->emitter->removeListener($this->event, $callback = $this->callback);
      call_user_func_array($callback, func_get_args());
    }
  }
}