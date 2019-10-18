<?php
declare(strict_types=1);

namespace Carica\Io\Event\Emitter {

  interface Listener {

    public function __invoke();

    public function getCallback(): callable;
  }
}
