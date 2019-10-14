<?php
declare(strict_types=1);

namespace Carica\Io\Event\Loop\StreamSelect {

  use Carica\Io\Event;
  use Carica\Io\Event\Loop as EventLoop;

  abstract class Listener implements Event\Loop\Listener {

    /**
     * @var EventLoop
     */
    private $_loop;

    abstract public function tick(): bool;

    public function __construct(EventLoop $loop) {
      $this->_loop = $loop;
    }

    public function getLoop(): EventLoop {
      return $this->_loop;
    }

    public function remove(): void {
      $this->_loop->remove($this);
    }
  }
}
