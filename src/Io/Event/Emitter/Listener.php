<?php

namespace Carica\Io\Event\Emitter {

  interface Listener {

    function __invoke();

    function getCallback();
  }
}