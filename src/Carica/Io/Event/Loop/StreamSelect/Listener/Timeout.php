<?php

namespace Carica\Io\Event\Loop\StreamSelect\Listener {

  class Timeout extends Interval {

    public function tick() {
      if (parent::tick()) {
        $this->getLoop()->remove($this);
        return TRUE;
      }
      return FALSE;
    }
  }
}