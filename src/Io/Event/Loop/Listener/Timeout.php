<?php

namespace Carica\Io\Event\Loop\Listener {

  use Carica\Io\Event;

  class Timeout extends Event\Loop\Listener\Interval {

    public function tick() {
      if (parent::tick()) {
        if ($loop = $this->Loop()) {
          $loop->remove($this);
        }
        return TRUE;
      }
      return FALSE;
    }
  }
}