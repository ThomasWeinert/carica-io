<?php

namespace Carica\Io\Event\Emitter {
  interface Listener {

    public function __invoke();

    public function getCallback();
  }
}
