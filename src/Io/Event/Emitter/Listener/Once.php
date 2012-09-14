<?php

namespace Carica\Io\Event\Emitter\Listener {

  use Carica\Io\Event;

  class Once extends On {

    public function __invoke() {
      $this->_emitter->removeListener($this->_event, $this->_callback());
      call_user_func_array($this->_callback, func_get_args());
    }
  }
}