<?php
declare(strict_types=1);

namespace Carica\Io\Event\Emitter\Listener {

  use Carica\Io\Event;

  /**
   * @property Event\Emitter $emitter
   * @property string $event
   * @property callable $callback
   */
  class Once extends On {

    public function __invoke(...$arguments) {
      $this->emitter->removeListener($this->event, $callback = $this->getCallback());
      $callback(...$arguments);
    }
  }
}
