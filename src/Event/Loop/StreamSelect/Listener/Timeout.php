<?php
declare(strict_types=1);

namespace Carica\Io\Event\Loop\StreamSelect\Listener {

  class Timeout extends Interval {

    public function tick(): bool {
      if (parent::tick()) {
        $this->getLoop()->remove($this);
        return TRUE;
      }
      return FALSE;
    }
  }
}
