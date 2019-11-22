<?php
declare(strict_types=1);

namespace Carica\Io\Event\Loop {

  use Carica\Io\Event\Loop as EventLoop;

  interface Listener {

    public function getLoop(): EventLoop;

    public function remove(): void;
  }
}
